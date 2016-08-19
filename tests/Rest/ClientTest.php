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
        $client = new Client(['baseUrl' => $url]);
        $this->assertEquals($url, $client->baseUrl);
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getHttpClient());
    }

    public function testGetOptions()
    {
        $client = new Client();
        $request = new Request([
            'entity' => 'test',
            'getParams' => ['foo' => 'bar'],
            'postParams' => ['bar' => 'foo'],
        ]);
        $this->assertEquals(
            [
                'headers' => [
                    'User-Agent' => 'Sima-land api-php-client/0.1',
                    'Content-Type' => 'application/json',
                ],
                'query' => ['foo' => 'bar'],
            ],
            $client->getOptions($request)
        );
    }

    public function testQuery()
    {
        $client = $this->getClient();
        $body = ['foo' => 'bar'];
        $this->setResponse($body);
        $result = $client->get('item');
        $this->assertEquals($body, $result);
        $body = 'raw body';
        $this->setGuzzleHttpResponse(new Response(200, [], $body));
        $result = $client->query('GET', 'item', ['key' => 'value']);
        $this->assertEquals(['html' => $body], $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidBatchQuery()
    {
        $client = $this->getClient();
        $client->batchQuery(['test']);
    }

    public function testBatchQuery()
    {
        $client = $this->getClient();
        $body1 = ['foo' => 'bar'];
        $body2 = ['bar' => 'foo'];
        $this->setResponse($body1);
        $this->setResponse($body2);
        $result = $client->batchQuery([
            new Request([
                'entity' => 'item',
                'getParams' => ['id-mf' => '2,0']
            ]),
            new Request([
                'entity' => 'item',
                'getParams' => ['id-mf' => '2,1']
            ]),
        ]);
        $this->assertEquals(array_merge($body1, $body2), $result);
    }
}
