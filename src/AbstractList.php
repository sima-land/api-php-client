<?php

namespace SimaLand\API;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Rest\Client;
use SimaLand\API\Rest\Request;
use SimaLand\API\Rest\ResponseException;
use GuzzleHttp\Exception\RequestException;

/**
 * Абстрактный класс для загрузки данных сущности.
 *
 * Класс реализует интерфейс Iterator.
 *
 * @property $getParams GET параметры запроса.
 */
abstract class AbstractList extends Object implements \Iterator
{
    /**
     * Кол-во потоков.
     *
     * @var int
     */
    public $countThreads = 5;

    /**
     * GET параметр отвечающий за поток.
     *
     * @var string
     */
    public $keyThreads = 'page';

    /**
     * Ключ альтернативной пагинации.
     *
     * @var string
     */
    public $keyAlternativePagination = 'id-greater-than';

    /**
     * Использовать альтернативную пагинацию.
     *
     * @var bool
     */
    public $useAlternativePagination = false;

    /**
     * GET параметры запроса.
     *
     * @var array
     */
    protected $_getParams = [];

    /**
     * Кол-во повторов обращение к ресурсу при ошибках.
     *
     * @var int
     */
    public $repeatTimeout = 30;

    /**
     * Время в секундак до следующего обращения к ресурсу.
     *
     * @var int
     */
    public $repeatCount = 30;


    /**
     * SimaLand кдиент для запросов.
     *
     * @var \SimaLand\API\Rest\Client
     */
    private $client;

    /**
     * Список запросов.
     *
     * @var Request[]
     */
    private $requests = [];

    /**
     * Список данных полученные по API.
     *
     * @var array
     */
    private $values = [];

    /**
     * Ключ текущей записи.
     *
     * @var int
     */
    private $key;

    /**
     * Текущая запись.
     *
     * @var mixed
     */
    private $current;

    /**
     * @param Client $client
     * @param array $options
     */
    public function __construct(Client $client, array $options = [])
    {
        $this->client = $client;
        parent::__construct($options);
    }

    /**
     * Получить наименование сущности.
     *
     * @return string
     */
    abstract public function getEntity();

    /**
     * Добавить get параметры.
     *
     * @param array $params
     * @return AbstractList
     */
    public function addGetParams(array $params)
    {
        $this->setGetParams(array_merge($this->_getParams, $params));
        return $this;
    }

    /**
     * Назначить следующую страницу запросу.
     *
     * @param Request $request
     * @param Record|null $record
     */
    public function assignPage(Request &$request, Record $record = null)
    {
        if ($this->useAlternativePagination) {
            $this->assignAlternativePage($request, $record);
        } else {
            $this->assignDefaultPage($request);
        }
    }

    /**
     * Назначить следующую страницу запросу, используя стандартную пагинацию.
     *
     * @param Request $request
     */
    protected function assignDefaultPage(Request &$request)
    {
        $currentPage = 1;
        if (!is_array($request->getParams)) {
            $request->getParams = (array)$request->getParams;
        }
        if (isset($request->getParams[$this->keyThreads])) {
            $currentPage = (int)$request->getParams[$this->keyThreads];
        }
        $request->getParams[$this->keyThreads] = $currentPage + $this->countThreads;
    }

    /**
     * Назначить следующую страницу запросу, используют альтернативную пагинацию.
     *
     * @param Request $request
     * @param Record|null $record
     */
    protected function assignAlternativePage(Request &$request, Record $record = null)
    {
        $lastId = 0;
        if ($record && $record->data) {
            $lastId = (int)$record->data['id'];
        }
        if (!is_array($request->getParams)) {
            $request->getParams = (array)$request->getParams;
        }
        $request->getParams[$this->keyAlternativePagination] = $lastId;
    }

    /**
     * Назначить номер потока для запроса.
     *
     * @param Request $request
     * @param int $number
     */
    public function assignThreadsNumber(Request &$request, $number = 0)
    {
        if ("id-mf" == $this->keyThreads) {
            $this->assignMfThreadsNumber($request, $number);
        } else {
            $this->assignDefaultThreadsNumber($request, $number);
        }
    }

    /**
     * Назначить по умолчанию номер потока для запроса.
     *
     * @param Request $request
     * @param int $number
     */
    public function assignDefaultThreadsNumber(Request &$request, $number = 0)
    {
        if (!is_array($request->getParams)) {
            $request->getParams = (array)$request->getParams;
        }
        if (!isset($request->getParams[$this->keyThreads])) {
            $request->getParams[$this->keyThreads] = 1;
        }
        $request->getParams[$this->keyThreads] += $number;
    }

    /**
     * Назначить альтернативный номер потока для запроса.
     *
     * @param Request $request
     * @param int $number
     */
    public function assignMfThreadsNumber(Request &$request, $number = 0)
    {
        if (!is_array($request->getParams)) {
            $request->getParams = (array)$request->getParams;
        }
        $request->getParams[$this->keyThreads] = "{$this->countThreads},$number";
    }

    /**
     * Наименование ключа содержащего набора данных сущности.
     *
     * @return string
     */
    public function getCollectionKey()
    {
        return 'items';
    }

    /**
     * Наименование ключа содержащего мета данные.
     *
     * @return string
     */
    public function getMetaKey()
    {
        return '_meta';
    }

    /**
     * Палучить набор данных сущности.
     *
     * @return Response[]
     * @throws \Exception
     */
    public function get()
    {
        return $this->client->batchQuery($this->getRequests());
    }

    /**
     * Установить запросы к API.
     *
     * @param Request[] $requests
     * @throws \Exception
     */
    public function setRequests(array $requests)
    {
        $this->requests = [];
        foreach ($requests as $request) {
            if (!$request instanceof Request) {
                throw new \Exception('Request must be implement "\SimaLand\API\Rest\Request"');
            }
            $this->requests[] = $request;
        }
    }

    /**
     * Получить запросы к API.
     *
     * @return array|Rest\Request[]
     */
    public function getRequests()
    {
        if (empty($this->requests)) {
            $requests = [];
            if (!is_null($this->keyThreads) && $this->countThreads > 1) {
                for ($i = 0; $i < $this->countThreads; $i++) {
                    $requests[$i] = new Request([
                        'entity' => $this->getEntity(),
                        'getParams' => $this->_getParams,
                    ]);
                    $this->assignThreadsNumber($requests[$i], $i);
                }
            } else {
                $requests[] = new Request([
                    'entity' => $this->getEntity(),
                    'getParams' => $this->_getParams,
                ]);
            }
            $this->requests = $requests;
        }
        return $this->requests;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        if (empty($this->values)) {
            $this->getData();
        }
        $this->current = array_shift($this->values);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->key++;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return !empty($this->current);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->values = [];
        $this->current = null;
        $this->key = 0;
        $this->next();
    }

    /**
     * Обработка ответов от API.
     *
     * @param Response[] $responses
     * @throws ResponseException
     */
    private function processingResponses(array $responses)
    {
        foreach ($responses as $response) {
            $statusCode = $response->getStatusCode();
            if (($statusCode < 200 || $statusCode >= 300) && $statusCode != 404) {
                throw new ResponseException($response);
            }
        }
    }

    /**
     * Получение ответов от API
     *
     * @return \GuzzleHttp\Psr7\Response[]
     * @throws \Exception
     */
    private function getResponses()
    {
        $i = 0;
        $responses = [];
        $logger = $this->getLogger();
        do {
            $e = null;
            if ($i > 0) {
                $logger->info("Wait time {$this->repeatTimeout} second to the next request");
                sleep($this->repeatTimeout);
                $attempt = $i + 1;
                $logger->info("Attempt {$attempt} of {$this->repeatCount}");
            }
            try {
                $responses = $this->get();
                $this->processingResponses($responses);
            } catch (\Exception $e) {
                if (
                    ($e instanceof RequestException) ||
                    ($e instanceof ResponseException)
                ) {
                    $logger->warning($e->getMessage(), ['code' => $e->getCode()]);
                } else {
                    throw $e;
                }
            }
            $i++;
        } while ($i <= $this->repeatCount && !is_null($e));
        if ($e) {
            $logger->error($e->getMessage(), ['code' => $e->getCode()]);
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $responses;
    }

    /**
     * Получить тело ответа от API.
     *
     * @param Response $response
     * @return bool
     */
    private function getBody(Response $response)
    {
        $body = json_decode($response->getBody(), true);
        if (
            !$body ||
            ($body && !isset($body[$this->getCollectionKey()]))
        ) {
            return false;
        }
        return $body;
    }

    /**
     * Получить набор данных от API.
     *
     * @throws Exception
     */
    private function getData()
    {
        $responses = $this->getResponses();
        $collectionKey = $this->getCollectionKey();
        $metaKey = $this->getMetaKey();
        $requests = $this->getRequests();
        $record = null;
        foreach ($responses as $key => $response) {
            $body = $this->getBody($response);
            if (!$body) {
                unset($requests[$key]);
                continue;
            }
            foreach ($body[$collectionKey] as $item) {
                $record = new Record([
                    'data' => $item,
                    'meta' => isset($body[$metaKey]) ? $body[$metaKey] : null,
                ]);
                $this->values[] = $record;
            }
            if (!is_null($record)) {
                $this->assignPage($requests[$key], $record);
            }
        }
        $this->setRequests($requests);
    }

    /**
     * Установить GET параметры запроса.
     *
     * @param array $value
     */
    public function setGetParams(array $value)
    {
        if (!isset($value[$this->keyAlternativePagination]) && $this->useAlternativePagination) {
            $value[$this->keyAlternativePagination] = 0;
        }
        $this->_getParams = $value;
    }

    /**
     * Получить GET параметры запроса.
     *
     * @return array
     */
    public function getGetParams()
    {
        return $this->_getParams;
    }
}
