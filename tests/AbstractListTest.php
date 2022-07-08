<?php

namespace SimaLand\API\Tests;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Exception;
use SimaLand\API\Rest\Request;

class AbstractListTest extends BaseCase
{
    private $category;
    private $item;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->category = require(TEST_DIR . "/data/category.php");
        $this->item = require(TEST_DIR . "/data/item.php");
    }

    /**
     * @return \SimaLand\API\AbstractList
     */
    private function getAbstractObject()
    {
        $class = 'SimaLand\API\AbstractList';
        $mock = $this->getMockBuilder($class)
            ->setConstructorArgs([$this->getClient(), ['logger' => $this->getLogger()]])
            ->getMockForAbstractClass();
        $mock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('entity'));
        return $mock;
    }

    public function testSetGetRequests()
    {
        $abstractObject = $this->getAbstractObject();

        $abstractObject->getParams = ['sort' => 'id'];
        $requests = $abstractObject->getRequests();
        $this->assertEquals($abstractObject->countThreads, count($requests));
        foreach ($requests as $request) {
            $this->assertArrayHasKey('sort', $request->getParams);
            $this->assertEquals('id', $request->getParams['sort']);
        }

        $abstractObject->setRequests([
            new Request(['entity' => 'item']),
            new Request(),
        ]);
        $requests = $abstractObject->getRequests();
        $this->assertEquals(2, count($requests));
    }

    public function testInvalidSetRequest()
    {
        $this->expectException(\Exception::class);
        $abstractObject = $this->getAbstractObject();
        $abstractObject->setRequests(['test']);
    }

    public function testAddGetParams()
    {
        $abstractObject = $this->getAbstractObject();
        $abstractObject->getParams = ['page' => 2];
        $abstractObject->addGetParams(['page' => 3, 'perPage' => 10]);
        $this->assertEquals(['page' => 3, 'perPage' => 10], $abstractObject->getParams);
    }

    public function testAssignPage()
    {
        $abstractObject = $this->getAbstractObject();
        $request = new \SimaLand\API\Rest\Request();
        $abstractObject->assignPage($request);
        $this->assertEquals(6, $request->getParams[$abstractObject->keyThreads]);
        $abstractObject->assignPage($request);
        $this->assertEquals(11, $request->getParams[$abstractObject->keyThreads]);
    }

    public function testAssignThreadNumber()
    {
        $abstractObject = $this->getAbstractObject();
        $request = new \SimaLand\API\Rest\Request();

        $abstractObject->assignThreadsNumber($request, 0);
        $this->assertEquals(1, $request->getParams[$abstractObject->keyThreads]);

        $request->getParams = [];
        $abstractObject->assignThreadsNumber($request, 1);
        $this->assertEquals(2, $request->getParams[$abstractObject->keyThreads]);

        $request->getParams = [];
        $abstractObject->assignThreadsNumber($request, 2);
        $this->assertEquals(3, $request->getParams[$abstractObject->keyThreads]);

        $abstractObject->getParams = ['page' => 5];
        $request->getParams = $abstractObject->getParams;
        $abstractObject->assignThreadsNumber($request, 0);
        $this->assertEquals(5, $request->getParams[$abstractObject->keyThreads]);

        $request->getParams = $abstractObject->getParams;
        $abstractObject->assignThreadsNumber($request, 1);
        $this->assertEquals(6, $request->getParams[$abstractObject->keyThreads]);
    }

    public function testGet()
    {
        $this->setResponse($this->item);
        $abstractObject = $this->getAbstractObject();
        $abstractObject->countThreads = 1;
        $response = $abstractObject->get();
        $response = reset($response);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
        $body = json_decode($response->getBody(), true);
        $this->assertEquals($this->item['items'], $body['items']);
    }

    public function testBatchGet()
    {
        $body1 = $body2 = $this->category;
        $countItems = count($body1['items']);
        for ($i = 0; $i < $countItems; $i++) {
            if ($i % 2 == 0) {
                unset($body1['items'][$i]);
            } else {
                unset($body2['items'][$i]);
            }
        }

        $this->setResponse($body1);
        $this->setResponse($body2);

        $abstractObject = $this->getAbstractObject();
        $abstractObject->keyThreads = 'id-mf';
        $abstractObject->countThreads = 2;

        $responses = $abstractObject->get();
        $this->assertEquals(2, count($responses));
        $response = array_shift($responses);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
        $body = json_decode($response->getBody(), true);
        $this->assertEquals($body1['items'], $body['items']);
        $response = array_shift($responses);
        $this->assertInstanceOf('GuzzleHttp\Psr7\Response', $response);
        $body = json_decode($response->getBody(), true);
        $this->assertEquals($body2['items'], $body['items']);
    }

    public function testIteration()
    {
        $this->setResponse($this->category);
        $this->setResponse($this->category);
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $abstractObject = $this->getAbstractObject();
        $abstractObject->countThreads = 1;
        foreach ($abstractObject as $item) {
            $this->assertInstanceOf('\SimaLand\API\Record', $item);
            $this->assertNotEmpty($item->data);
            $this->assertArrayHasKey('id', $item->data);
            $this->assertNotEmpty($item->meta);
            $this->assertEquals(44752, $item->meta['totalCount']);
        }
    }

    public function testExceptionRepeat()
    {
        $this->expectException(\SimaLand\API\Exception::class);
        $this->setGuzzleHttpResponse(new Response(500, [], 'Internal Server Error'));
        $this->setGuzzleHttpResponse(new Response(500, [], 'Internal Server Error'));
        $abstractObject = $this->getAbstractObject();
        $abstractObject->countThreads = 1;
        $abstractObject->repeatTimeout = 1;
        $abstractObject->repeatCount = 1;
        $abstractObject->next();
    }

    public function testRepeat()
    {
        $this->setGuzzleHttpResponse(new Response(500, [], 'Internal Server Error'));
        $this->setResponse($this->category);
        $this->setResponse($this->category);
        $this->setResponse($this->category);

        $abstractObject = $this->getAbstractObject();
        $abstractObject->countThreads = 2;
        $abstractObject->repeatTimeout = 1;
        $abstractObject->repeatCount = 1;
        $abstractObject->next();
        $current = $abstractObject->current();
        $this->assertInstanceOf('\SimaLand\API\Record', $current);
    }

    public function testListException()
    {
        $this->expectException(\Exception::class);
        $this->setGuzzleHttpResponse(function () {
            throw new Exception('Test exception');
        });
        $abstractObject = $this->getAbstractObject();
        $abstractObject->countThreads = 1;
        $abstractObject->repeatCount = 1;
        $abstractObject->next();
    }

    public function testSetGetParams()
    {
        $abstractObject = $this->getAbstractObject();
        $keyPage = $abstractObject->keyThreads;
        $newKeyPage = "test";
        $abstractObject->keyThreads = $newKeyPage;
        $abstractObject->useAlternativePagination = true;
        $abstractObject->setGetParams([$keyPage => 20]);
        $this->assertArrayNotHasKey($keyPage, $abstractObject->getGetParams());
    }

    public function testCleanData()
    {
        $this->setResponse($this->category);
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $abstractObject = $this->getAbstractObject();
        $abstractObject->countThreads = 1;
        $abstractObject->fields = ['id', 'name'];
        foreach ($abstractObject as $key => $value) {
            $sourceData = [
                'id' => $this->category['items'][$key]['id'],
                'name' => $this->category['items'][$key]['name'],
            ];
            $this->assertEquals($value->data, $sourceData);
        }
    }
}
