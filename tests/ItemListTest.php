<?php

namespace SimaLand\API\Tests;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Entities\ItemList;
use SimaLand\API\Record;

class ItemListTest extends BaseCase
{
    public function testAssignPage()
    {
        $itemList = new ItemList($this->getClient());
        $request = new \SimaLand\API\Rest\Request();

        $itemList->assignPage($request);
        $this->assertEmpty($request->getParams);

        $record = new Record();
        $record->data['id'] = 100;
        $itemList->assignPage($request, $record);
        $this->assertEquals(100, $request->getParams[$itemList->keyAlternativePagination]);

        $itemList->assignThreadsNumber($request, 1);
        $record->data['id'] = 200;
        $itemList->assignPage($request, $record);
        $this->assertEquals(200, $request->getParams[$itemList->keyAlternativePagination]);
        $this->assertEquals('5,1', $request->getParams[$itemList->keyThreads]);
    }

    public function testAssignThreadNumber()
    {
        $itemList = new ItemList($this->getClient());
        $itemList->countThreads = 5;
        $request = new \SimaLand\API\Rest\Request();
        $itemList->assignThreadsNumber($request, 0);
        $this->assertEquals('5,0', $request->getParams[$itemList->keyThreads]);
        $itemList->assignThreadsNumber($request, 1);
        $this->assertEquals('5,1', $request->getParams[$itemList->keyThreads]);
    }

    public function testGet()
    {
        $itemData = require(TEST_DIR . "/data/item.php");
        unset($itemData['_links']);
        unset($itemData['_meta']);
        for ($i = 0; $i < 5; $i++) {
            $this->setResponse($itemData);
        }
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));


        for ($i = 0; $i < 5; $i++) {
            $this->setResponse($itemData);
        }

        $items = new ItemList($this->getClient());
        $items->countThreads = 2;
        $items->keyThreads = 'id-mf';
        $result = [];
        foreach ($items as $key => $item) {
            $result[] = $item;
        }
        $this->assertEquals(30, count($result));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Internal Server Error
     */
    public function testException()
    {
        $this->setGuzzleHttpResponse(new Response(500, [], 'Invalid params'));
        $items = new ItemList($this->getClient());
        $items->countThreads = 1;
        $items->repeatCount = 0;
        $items->rewind();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid params
     */
    public function testMessageException()
    {
        $this->setResponse(['message' => 'Invalid params'], 500);
        $items = new ItemList($this->getClient());
        $items->countThreads = 1;
        $items->repeatCount = 0;
        $items->rewind();
    }
}
