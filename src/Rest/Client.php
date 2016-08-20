<?php

namespace SimaLand\API\Rest;

use \GuzzleHttp\ClientInterface;
use SimaLand\API\Object;

/**
 * @link https://www.sima-land.ru/api/v3/help/
 */
class Client extends Object
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
    private $httpClient;

    /**
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new \GuzzleHttp\Client();
        }
        return $this->httpClient;
    }

    /**
     * @param \GuzzleHttp\ClientInterface $httpClient
     * @return \SimaLand\API\Rest\Client
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * @param array $requests
     * @return Response[]
     * @throws \Exception
     */
    public function batchQuery(array $requests)
    {
        $client = $this->getHttpClient();
        $promises = [];
        foreach ($requests as $name => $request){
            if (!($request instanceof Request)) {
                throw new \Exception('Request must be implement "\SimaLand\API\Rest\Request"');
            }
            $url = $this->createUrl($request->entity);
            $promises[$name] = $client->requestAsync(
                $request->method,
                $url,
                $this->getOptions($request)
            );
        }
        $responses = \GuzzleHttp\Promise\unwrap($promises);
        return $this->parseResponses($responses);
    }

    /**
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
     * @param string $entity
     * @param array $getParams
     * @return mixed|string
     */
    public function get($entity, array $getParams = [])
    {
        return $this->query('GET', $entity, $getParams);
    }

    /**
     * @param \GuzzleHttp\Psr7\Response[] $responses
     * @return Response[]
     */
    private function parseResponses(array $responses)
    {
        $result = [];
        foreach ($responses as $key => $response) {
            $body = $response->getBody()->getContents();
            $contentType = $response->getHeader('Content-Type');
            if (!empty($contentType)) {
                $contentType = reset($contentType);
            } else {
                $contentType = '';
            }
            $result[$key] = new Response($body, $contentType);
        }
        return $result;
    }

    /**
     * @param Request|null $request
     * @return array
     */
    public function getOptions(Request $request = null)
    {
        $options = [];
        if (!is_null($request)) {
            if (!empty($request->getParams)) {
                $options['query'] = $request->getParams;
            }
        }
        return array_merge(
            [
                'headers' => [
                    'User-Agent' => 'Sima-land api-php-client/0.1',
                    'Content-Type' => 'application/json',
                ],
            ],
            $options
        );
    }

    /**
     * Generate url of the baseUrl, entity and get params
     *
     * @param string $entity
     * @return string
     */
    private function createUrl($entity)
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
        return $url . $entity;
    }
}
