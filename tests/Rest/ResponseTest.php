<?php

namespace SimaLand\API\Tests\Rest;

use SimaLand\API\Rest\Response;
use SimaLand\API\Tests\BaseCase;

class ResponseTest extends BaseCase
{
    public function testParse()
    {
        $body = 'raw body';
        $guzzleResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/text'], $body);
        $response = new Response($guzzleResponse);
        $this->assertEquals($body, $response->rawBody);
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(['Content-Type' => [0 => 'application/text']], $response->headers);
        $this->assertNull($response->body);

        $body = require(TEST_DIR . "/data/item.php");
        $rawBody = json_encode($body);
        $guzzleResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], $rawBody);
        $response = new Response($guzzleResponse);
        $this->assertEquals($rawBody, $response->rawBody);
        $this->assertEquals($body, $response->body);
    }

    public function testIsOk()
    {
        $guzzleResponse = new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/text'], 'Ok');
        $response = new Response($guzzleResponse);
        $this->assertTrue($response->isOk());

        $guzzleResponse = new \GuzzleHttp\Psr7\Response(404, ['Content-Type' => 'application/text'], 'Not found');
        $response = new Response($guzzleResponse);
        $this->assertFalse($response->isOk());
    }
}
