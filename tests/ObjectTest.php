<?php

namespace SimaLand\API\Tests;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use SimaLand\API\BaseObject;
use SimaLand\API\Tests\models\TestModel;

class ObjectTest extends BaseCase
{
    public function testGetSetLogger()
    {
        $object = new BaseObject();
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $object->getLogger());

        $logger = new Logger(BaseObject::LOGGER_NAME);
        $logger->pushHandler(new NullHandler());
        $object = new BaseObject(['logger' => $logger]);
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $object->getLogger());

        $object = new BaseObject();
        $object->setLogger($logger);
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $object->getLogger());
    }

    public function testSetGet()
    {
        $testModel = new TestModel();
        $testModel->name = "name";
        $testModel->sid = 123;

        $this->assertEquals("Test_name", $testModel->name);
        $this->assertEquals(123, $testModel->sid);
    }
}
