<?php

namespace SimaLand\API\Rest;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\Response;
use SimaLand\API\Object;

/**
 * SimaLand клиент.
 *
 * @link https://www.sima-land.ru/api/v3/help/
 */
class Client extends Object
{
    /**
     * Базовый url API sima-land.ru.
     *
     * @var string
     */
    public $baseUrl = 'https://www.sima-land.ru/api/v3';

    /**
     * @var string
     */
    public $login;

    /**
     * @var string
     */
    public $password;

    /**
     * Путь до токена.
     *
     * @var string
     */
    public $tokenPath;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $token;

    /**
     * Базовые опции http запроса к API
     *
     * @var array
     */
    private $options = [
        'http_errors' => false,
        'headers' => [
            'User-Agent' => 'Sima-land api-php-client/3.0',
            'Content-Type' => 'application/json',
        ],
    ];

    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['login'])) {
            throw new \Exception('Login can`t be empty');
        }
        if (!isset($options['password'])) {
            throw new \Exception('Password can`t be empty');
        }
        parent::__construct($options);
    }

    /**
     * Получить http клиент.
     *
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new \GuzzleHttp\Client();
        }
        return $this->httpClient;
    }

    /**
     * Установить http клиент.
     *
     * @param \GuzzleHttp\ClientInterface $httpClient
     * @return Client
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Групповой запрос к API.
     *
     * @param array $requests
     * @return Response[]
     * @throws \Exception
     */
    public function batchQuery(array $requests)
    {
        $client = $this->getHttpClient();
        $promises = [];
        foreach ($requests as $name => $request) {
            if (!($request instanceof Request)) {
                throw new \Exception('Request must be implement "\SimaLand\API\Rest\Request"');
            }
            $url = $this->createUrl($request->entity);
            $this->getLogger()->info(
                "Send request {$url}",
                [
                    'getParams' => $request->getParams,
                ]
            );
            $promises[$name] = $client->requestAsync(
                $request->method,
                $url,
                $this->getOptions($request)
            );
        }
        /** @var \GuzzleHttp\Psr7\Response[] $responses */
        $responses = \GuzzleHttp\Promise\unwrap($promises);
        foreach ($responses as $key => $response) {
            if ($response->getStatusCode() == 401) {
                $this->deleteToken();
                return $this->batchQuery($requests);
            }
        }
        return $responses;
    }

    /**
     * Запрос к API.
     *
     * @param string $method
     * @param string $entity
     * @param array $getParams
     * @return Response
     * @throws \Exception
     */
    public function query($method, $entity, array $getParams = [])
    {
        $response = $this->batchQuery([
            new Request([
                'entity' => $entity,
                'method' => $method,
                'getParams' => $getParams,
            ])
        ]);
        return reset($response);
    }

    /**
     * GET запрос к API.
     *
     * @param string $entity
     * @param array $getParams
     * @return Response
     */
    public function get($entity, array $getParams = [])
    {
        return $this->query('GET', $entity, $getParams);
    }

    /**
     * Удалить файл с токеном.
     *
     * @return Client
     * @throws \Exception
     */
    public function deleteToken()
    {
        $this->token = null;
        $filename = $this->getTokenFilename();
        if (file_exists($filename)) {
            unlink($filename);
        }
        return $this;
    }

    /**
     * Получить опции для http клиента.
     *
     * @param Request|null $request
     * @return array
     */
    public function getOptions(Request $request = null)
    {
        $options = [];
        if (!is_null($request)) {
            if (!empty($request->getParams)) {
                $options[RequestOptions::QUERY] = $request->getParams;
            }
        }
        $options = array_merge($this->options, $options);
        $options['headers']['Authorization'] = 'Bearer ' . $this->getToken();
        return $options;
    }

    /**
     * Аутентификация пользователя.
     *
     * @throws \Exception
     */
    private function auth()
    {
        $client = $this->getHttpClient();
        $options = $this->options;
        $options['headers']['Authorization'] = 'Basic ' . base64_encode($this->login . ":" . $this->password);
        $response = $client->request('GET', $this->createUrl('auth'), $options);
        if ($response->getStatusCode() != 200) {
            throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
        }
        $response->getStatusCode();
        $body = json_decode($response->getBody(), true);
        $this->token = $body['jwt'];
        file_put_contents($this->getTokenFilename(), $body['jwt']);
    }

    /**
     * Создания url к сущности.
     *
     * @param string $entity
     * @return string
     */
    private function createUrl($entity)
    {
        $url = $this->baseUrl;
        $urlLen = strlen($url);
        $entityLen = strlen($entity);
        if ($url[$urlLen - 1] != '/' && $entity[0] != '/') {
            $url .= "/";
        }
        if ($entity[$entityLen - 1] != '/') {
            $entity .= "/";
        }
        return $url . $entity;
    }

    /**
     * Получить полный путь до токена.
     *
     * @return string
     * @throws \Exception
     */
    private function getTokenFilename()
    {
        if (is_null($this->tokenPath)) {
            $this->tokenPath = sys_get_temp_dir();
        }
        if (!file_exists($this->tokenPath)) {
            throw new \Exception("Path {$this->tokenPath} not found");
        }
        if (substr($this->tokenPath, -1) != '/') {
            $this->tokenPath .= '/';
        }
        return $this->tokenPath . 'token.txt';
    }

    /**
     * Получить токен.
     *
     * @return string
     * @throws \Exception
     */
    private function getToken()
    {
        if (is_null($this->token)) {
            $filename = $this->getTokenFilename();
            if (file_exists($filename)) {
                $this->token = file_get_contents($filename);
            } else {
                $this->auth();
            }
        }
        return $this->token;
    }
}
