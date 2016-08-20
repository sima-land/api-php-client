<?php

namespace SimaLand\API\Tests\Rest;

use SimaLand\API\Rest\Response;
use SimaLand\API\Tests\BaseCase;

class ResponseTest extends BaseCase
{
    public function testParse()
    {
        $body = 'raw body';
        $response = new Response($body);
        $this->assertEquals($body, $response->rawBody);

        $body = require(__DIR__ . "/../data/item.php");
        $invalidBody = $body;

        $countItems = count($body['items']);
        $totalCount = $body['_meta']['totalCount'];
        $pageCount = $body['_meta']['pageCount'];
        $currentPage = $body['_meta']['currentPage'];
        $perPage = $body['_meta']['perPage'];

        unset($invalidBody['_meta']);
        $invalidBody = json_encode($invalidBody);
        $response = new Response($invalidBody, 'application/json');
        $this->assertEquals($invalidBody, $response->rawBody);
        $this->assertEquals($countItems, count($response->items));
        $this->assertEquals(0, $response->totalCount);
        $this->assertEquals(0, $response->pageCount);
        $this->assertEquals(1, $response->currentPage);
        $this->assertEquals(50, $response->perPage);

        $body = json_encode($body);
        $response = new Response($body, 'application/json');
        $this->assertEquals($body, $response->rawBody);
        $this->assertEquals($countItems, count($response->items));
        $this->assertEquals($totalCount, $response->totalCount);
        $this->assertEquals($pageCount, $response->pageCount);
        $this->assertEquals($currentPage, $response->currentPage);
        $this->assertEquals($perPage, $response->perPage);
    }
}
