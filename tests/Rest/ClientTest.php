<?php

namespace SimaLand\API\Tests\Rest;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Rest\Client;
use SimaLand\API\Rest\Request;
use SimaLand\API\Tests\BaseCase;

class ClientTest extends BaseCase
{
    public function testCreateClient()
    {
        $url = 'http://example.com';
        $client = new Client(['baseUrl' => $url, 'login' => 'test', 'password' => 'password']);
        $this->assertEquals($url, $client->baseUrl);
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getHttpClient());
    }

    public function testGetOptions()
    {
        $client = new Client([
            'login' => 'test',
            'password' => 'password',
            'tokenPath' => TEST_DIR . "data"
        ]);
        $request = new Request([
            'entity' => 'test',
            'getParams' => ['foo' => 'bar'],
            'postParams' => ['bar' => 'foo'],
        ]);
        $options = $client->getOptions($request);
        unset($options['headers']['User-Agent']);
        $this->assertEquals(
            [
                'http_errors' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer token',
                ],
                'query' => ['foo' => 'bar'],
                'json' => ['bar' => 'foo']
            ],
            $options
        );
    }

    public function testEmptyLogin()
    {
        $this->expectException(\Exception::class);
        new Client();
    }

    public function testEmptyPassword()
    {
        $this->expectException(\Exception::class);
        new Client(['login' => 'test']);
    }

    public function testInvalidLoginPassword()
    {
        $this->expectException(\Exception::class);
        $client = $this->getClient();
        $this->setGuzzleHttpResponse(new Response(401, [], 'Unauthorized'));
        $oldTokenPath = $client->tokenPath;
        $client->tokenPath = null;
        $client->get('user');
        $client->tokenPath = $oldTokenPath;
    }

    public function testInvalidTokenPath()
    {
        $this->expectException(\Exception::class);
        $client = new Client([
            'login' => 'test',
            'password' => 'password',
            'tokenPath' => TEST_DIR . 'fake',
            'logger' => $this->getLogger()
        ]);
        $client->get('user');
    }

    public function testDeleteToken()
    {
        $filename = TEST_DIR . 'output/token.txt';
        file_put_contents($filename, 'token');
        $client = new Client([
            'login' => 'test',
            'password' => 'password',
            'tokenPath' => TEST_DIR . 'output'
        ]);
        $client->deleteToken();
        $this->assertFileDoesNotExist($filename);
    }

    public function testGetToken()
    {
        $token = uniqid();
        $tokenPath = TEST_DIR . 'output';
        $fileToken = $tokenPath . "/token.txt";
        file_put_contents($fileToken, 'token');

        $this->setGuzzleHttpResponse(new Response(401, [], 'Unauthorized'));
        $this->setResponse(['jwt' => $token]);
        $this->setGuzzleHttpResponse(new Response(200, [], 'ok'));

        $client = $this->getClient();
        $oldTokenPath = $client->tokenPath;
        $client->tokenPath = $tokenPath;
        $response = $client->get('user');
        $client->tokenPath = $oldTokenPath;
        $this->assertEquals('ok', $response->getBody()->getContents());
        $this->assertEquals($token, file_get_contents($fileToken));
        @unlink($fileToken);
    }

    public function testQuery()
    {
        $client = $this->getClient();
        $body = ['items' => ['foo' => 'bar']];
        $this->setResponse($body);
        $response = $client->get('item');
        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
        $responseBody = json_decode($response->getBody(), true);
        $this->assertEquals($body['items'], $responseBody['items']);
    }

    public function testInvalidBatchQuery()
    {
        $this->expectException(\Exception::class);
        $client = $this->getClient();
        $client->batchQuery(['test']);
    }

    public function testBatchQuery()
    {
        $client = $this->getClient();
        $body = [
            'item1' => ['items' => ['foo' => 'bar']],
            'item2' => ['items' => ['bar' => 'foo']]
        ];
        foreach ($body as $item) {
            $this->setResponse($item);
        }
        $responses = $client->batchQuery([
            'item1' => new Request([
                'entity' => 'item',
                'getParams' => ['id-mf' => '2,0']
            ]),
            'item2' => new Request([
                'entity' => 'item',
                'getParams' => ['id-mf' => '2,1']
            ]),
        ]);
        foreach ($responses as $key => $response) {
            $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
            $responseBody = json_decode($response->getBody(), true);
            $this->assertEquals($body[$key]['items'], $responseBody['items']);
        }
    }
}
