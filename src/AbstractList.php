<?php

namespace SimaLand\API;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Rest\Client;
use SimaLand\API\Rest\Request;

/**
 * Абстрактный класс для загрузки данных сущности.
 *
 * Класс реализует интерфейс Iterator.
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
     * GET параметры запроса.
     *
     * @var array
     */
    public $getParams = [];

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
        $this->getParams = array_merge($this->getParams, $params);
        return $this;
    }

    /**
     * Назначить следующию страницу запросу.
     *
     * @param Request $request
     * @param Record|null $record
     */
    public function assignPage(Request &$request, Record $record = null)
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
     * Назначить номер потока для запроса.
     *
     * @param Request $request
     * @param int $number
     */
    public function assignThreadsNumber(Request &$request, $number = 0)
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
                        'getParams' => $this->getParams,
                    ]);
                    $this->assignThreadsNumber($requests[$i], $i);
                }
            } else {
                $requests[] = new Request([
                    'entity' => $this->getEntity(),
                    'getParams' => $this->getParams,
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
     * Получить тело ответа от API.
     *
     * @param Response $response
     * @return bool
     * @throws \Exception
     */
    private function getBody(Response $response)
    {
        $body = json_decode($response->getBody(), true);
        $statusCode = $response->getStatusCode();
        if (($statusCode < 200 || $statusCode >= 300) && $statusCode != 404) {
            if ($body && isset($body['message'])) {
                $message = $body['message'];
            } else {
                $message = $response->getReasonPhrase();
            }
            throw new \Exception($message, $statusCode);
        } elseif (
            $statusCode == 404 ||
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
     * @throws \Exception
     */
    private function getData()
    {
        $responses = $this->get();
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
}
