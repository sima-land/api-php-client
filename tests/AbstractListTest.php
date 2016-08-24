<?php

namespace SimaLand\API\Tests;

use SimaLand\API\Rest\Request;

class AbstractListTest extends BaseCase
{
    private $category;
    private $item;

    /**
     * @inheritdoc
     */
    public function setUp()
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
            ->setConstructorArgs(['client' => $this->getClient()])
            ->getMockForAbstractClass();
        $mock->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('entity'));
        $mock->expects($this->any())
            ->method('getQueryNextPage')
            ->will($this->returnValue([]));
        return $mock;
    }

    public function testSetGetRequests()
    {
        $abstractObject = $this->getAbstractObject();
        $abstractObject->setRequests([
            new Request(['entity' => 'item']),
            new Request(),
        ]);
        $requests = $abstractObject->getRequests();
        $this->assertEquals(2, count($requests));
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidSetRequest()
    {
        $abstractObject = $this->getAbstractObject();
        $abstractObject->setRequests(['test']);
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
        $this->assertEmpty($request->getParams);
        $abstractObject->assignThreadsNumber($request, 1);
        $this->assertEquals(2, $request->getParams[$abstractObject->keyThreads]);
        $abstractObject->assignThreadsNumber($request, 2);
        $this->assertEquals(3, $request->getParams[$abstractObject->keyThreads]);
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
}
