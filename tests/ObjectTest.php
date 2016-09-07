<?php

namespace SimaLand\API\Tests;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use SimaLand\API\Object;

class ObjectTest extends BaseCase
{
    public function testGetSetLogger()
    {
        $object = new Object();
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $object->getLogger());

        $logger = new Logger(Object::LOGGER_NAME);
        $logger->pushHandler(new NullHandler());
        $object = new Object(['logger' => $logger]);
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $object->getLogger());

        $object = new Object();
        $object->setLogger($logger);
        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $object->getLogger());
    }
}
