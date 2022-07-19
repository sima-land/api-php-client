<?php

namespace SimaLand\API\Tests;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Entities\CategoryList;
use SimaLand\API\Rest\Request;
use SimaLand\API\Record;

class CategoryListTest extends BaseCase
{
    public function testSetGetParams()
    {
        $list = new CategoryList(
            $this->getClient(),
            [
                'logger' => $this->getLogger(),
                'getParams' => ["test" => "test"],
            ]
        );
        $this->assertEquals(["test" => "test", "id-greater-than" => 0], $list->getParams);
    }

    public function testAssignPage()
    {
        $list = new CategoryList(
            $this->getClient(),
            [
                'logger' => $this->getLogger(),
                'getParams' => ["test" => "test"],
            ]
        );
        $request = new Request();
        $request->getParams = $list->getParams;

        $list->assignPage($request);
        $this->assertEquals(["test" => "test", "id-greater-than" => 0], $request->getParams);

        $record = new Record();
        $record->data['id'] = 100;
        $list->assignPage($request, $record);
        $this->assertEquals(100, $request->getParams[$list->keyAlternativePagination]);

        $list->assignThreadsNumber($request, 1);
        $record->data['id'] = 200;
        $list->assignPage($request, $record);
        $this->assertEquals(200, $request->getParams[$list->keyAlternativePagination]);
        $this->assertEquals('5,1', $request->getParams[$list->keyThreads]);
    }

    public function testAssignThreadNumber()
    {
        $list = new CategoryList($this->getClient(), ['logger' => $this->getLogger()]);
        $list->countThreads = 5;
        $request = new Request();
        $list->assignThreadsNumber($request, 0);
        $this->assertEquals('5,0', $request->getParams[$list->keyThreads]);
        $list->assignThreadsNumber($request, 1);
        $this->assertEquals('5,1', $request->getParams[$list->keyThreads]);
    }

    public function testGet()
    {
        $categoryData = require(TEST_DIR . "/data/category.php");
        unset($categoryData['_links']);
        unset($categoryData['_meta']);
        for ($i = 0; $i < 5; $i++) {
            $this->setResponse($categoryData);
        }
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));


        for ($i = 0; $i < 5; $i++) {
            $this->setResponse($categoryData);
        }

        $list = new CategoryList($this->getClient(), ['logger' => $this->getLogger()]);
        $list->countThreads = 2;
        $list->keyThreads = 'id-mf';
        $result = [];
        foreach ($list as $key => $item) {
            $result[] = $item;
        }
        $this->assertEquals(25, count($result));
    }

    public function testException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Internal Server Error");
        $this->setGuzzleHttpResponse(new Response(500, [], 'Invalid params'));
        $list = new CategoryList($this->getClient(), ['logger' => $this->getLogger()]);
        $list->countThreads = 1;
        $list->repeatCount = 0;
        $list->rewind();
    }

    public function testMessageException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid params");
        $this->setResponse(['message' => 'Invalid params'], 500);
        $list = new CategoryList($this->getClient(), ['logger' => $this->getLogger()]);
        $list->countThreads = 1;
        $list->repeatCount = 0;
        $list->rewind();
    }
}
