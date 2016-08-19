<?php

namespace SimaLand\API\Tests;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Client;

class ClientTest extends BaseCase
{
    public function testCreateClient()
    {
        $url = 'http://example.com';
        $client = new Client(['baseUrl' => $url]);
        $this->assertEquals($url, $client->baseUrl);
        $this->assertInstanceOf('\GuzzleHttp\Client', $client->getHttpClient());
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
        $this->assertEquals($body, $result);
    }
}
