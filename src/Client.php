<?php

namespace simaland\apiClient;

use \GuzzleHttp\ClientInterface;

/**
 * @link https://www.sima-land.ru/api/v3/help/
 */
class Client
{
    /**
     * Base url sima-land.ru
     *
     * @var string
     */
    public $baseUrl = 'https://www.sima-land.ru/api/v3';

    /**
     * @var \GuzzleHttp\Client
     */
    private $_httpClient;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if (is_null($this->_httpClient)) {
            $this->_httpClient = new \GuzzleHttp\Client();
        }
        return $this->_httpClient;
    }

    /**
     * @param \GuzzleHttp\ClientInterface $httpClient
     * @return \simaland\apiClient\Client
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    /**
     * @param string $method
     * @param string $entity
     * @param array $getParams
     * @return mixed|string
     */
    public function query($method, $entity, array $getParams = [])
    {
        $client = $this->getHttpClient();
        $request = $client->request($method, $this->_getUrl($entity, $getParams), $this->_getOptions());
        return $this->_parseRequest($request);
    }

    /**
     * @param string $entity
     * @param array $getParams
     * @return mixed|string
     */
    public function get($entity, array $getParams = [])
    {
        return $this->query('GET', $entity, $getParams);
    }

    /**
     * @param \GuzzleHttp\Psr7\Response $request
     * @return mixed|string
     */
    private function _parseRequest(\GuzzleHttp\Psr7\Response $request)
    {
        $body = $request->getBody()->getContents();
        $contentType = $request->getHeader('Content-Type');
        if (!empty($contentType)) {
            $contentType = reset($contentType);
        } else {
            $contentType = '';
        }
        if (stripos($contentType, 'application/json') !== false) {
            $body = \GuzzleHttp\json_decode($body, true);
        }
        return $body;
    }

    /**
     * @return array
     */
    private function _getOptions()
    {
        return [
            'headers' => [
                'User-Agent' => 'Sima-land api-php-client/0.1',
                'Content-Type' => 'application/json',
            ],
        ];
    }

    /**
     * Generate url of the baseUrl, entity and get params
     *
     * @param string $entity
     * @param array $getParams
     * @return string
     */
    private function _getUrl($entity, array $getParams = [])
    {
        $url = $this->baseUrl;
        $urlLen = strlen($url);
        $entityLen = strlen($entity);
        if ($url[$urlLen - 1] != '/' and $entity[0] != '/') {
            $url .= "/";
        }
        if ($entity[$entityLen - 1] != '/') {
            $entity .= "/";
        }
        $url .= $entity;
        if (!empty($getParams)) {
            $url .= "?" . http_build_query($getParams);
        }
        return $url;
    }
}
