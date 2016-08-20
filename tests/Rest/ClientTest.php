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
        $body = ['items' => ['foo' => 'bar']];
        $this->setResponse($body);
        $response = $client->get('item');
        $this->assertInstanceOf('SimaLand\API\Rest\Response', $response);
        $this->assertEquals($body['items'], $response->items);

        $body = 'raw body';
        $this->setGuzzleHttpResponse(new Response(200, [], $body));
        $response = $client->query('GET', 'item', ['key' => 'value']);
        $this->assertInstanceOf('SimaLand\API\Rest\Response', $response);
        $this->assertEmpty($response->items);
        $this->assertEquals($body, $response->rawBody);
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
            $this->assertInstanceOf('SimaLand\API\Rest\Response', $response);
            $this->assertEquals($body[$key]['items'], $response->items);
        }
    }
}
