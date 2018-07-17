<?php

namespace SimaLand\API\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use SimaLand\API\BaseObject;
use SimaLand\API\Rest\Client;

class BaseCase extends \PHPUnit_Framework_TestCase
{
    /** @var \SimaLand\API\Rest\Client */
    private $client;

    /** @var \GuzzleHttp\Handler\MockHandler */
    private $mockGuzzleHandler;

    /**
     * @return Logger
     */
    public function getLogger()
    {
        $logger = new Logger(BaseObject::LOGGER_NAME);
        $logger->pushHandler(new NullHandler());
        return $logger;
    }

    /**
     * @return \GuzzleHttp\Handler\MockHandler
     */
    private function getMockGuzzleHandler()
    {
        if (is_null($this->mockGuzzleHandler)) {
            $this->mockGuzzleHandler = new MockHandler();
        }
        return $this->mockGuzzleHandler;
    }

    /**
     * @param mixed $response
     */
    protected function setGuzzleHttpResponse($response)
    {
        $this->getMockGuzzleHandler()->append($response);
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
     * @return \SimaLand\API\Rest\Client
     */
    protected function getClient()
    {
        if (is_null($this->client)) {
            $handler = HandlerStack::create($this->getMockGuzzleHandler());
            $guzzleClient = new \GuzzleHttp\Client(['handler' => $handler]);
            $this->client = new Client([
                'login' => 'test',
                'password' => 'password',
                'tokenPath' => TEST_DIR . 'data',
                'logger' => $this->getLogger(),
            ]);
            $this->client->setHttpClient($guzzleClient);
        }
        return $this->client;
    }
}
