<?php

namespace SimaLand\API\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SimaLand\API\Client;

class BaseCase extends TestCase
{
    /** @var \SimaLand\API\Client */
    private $_client;

    /** @var \GuzzleHttp\Handler\MockHandler */
    private $_mockGuzzleHandler;

    /**
     * @return \GuzzleHttp\Handler\MockHandler
     */
    private function _getMockGuzzleHandler()
    {
        if (is_null($this->_mockGuzzleHandler)) {
            $this->_mockGuzzleHandler = $this->_mockGuzzleHandler = new MockHandler();
        }
        return $this->_mockGuzzleHandler;
    }

    /**
     * @param mixed $response
     */
    protected function setGuzzleHttpResponse($response)
    {
        $this->_getMockGuzzleHandler()->append($response);
    }

    /**
     * @param array $body
     * @param int $statusCode
     */
    protected function setResponse(array $body, $statusCode = 200)
    {
        $body = json_encode($body);
        $this->setGuzzleHttpResponse(
            new Response($statusCode, ['Content-Type' => 'application/json'], $body)
        );
    }

    /**
     * @return \SimaLand\API\Client
     */
    protected function getClient()
    {
        if (is_null($this->_client)) {
            $handler = HandlerStack::create($this->_getMockGuzzleHandler());
            $guzzleClient = new \GuzzleHttp\Client(['handler' => $handler]);
            $this->_client = new Client();
            $this->_client->setHttpClient($guzzleClient);
        }
        return $this->_client;
    }
}
