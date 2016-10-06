<?php

namespace SimaLand\API\Tests\Parser;

use SimaLand\API\Parser\Json;
use SimaLand\API\Record;
use SimaLand\API\Tests\BaseCase;

class JsonTest extends BaseCase
{
    public function testSave()
    {
        $item = require(TEST_DIR . "/data/item.php");
        $expectedFile = TEST_DIR . 'data/item.txt';
        $actualFile = TEST_DIR . 'output/item.txt';
        @unlink($actualFile);

        $storage = new Json(['filename' => $actualFile]);
        foreach ($item['items'] as $item) {
            $record = new Record(['data' => $item]);
            $storage->save($record);
        }

        $this->assertEquals(md5_file($expectedFile), md5_file($actualFile));

        @unlink($actualFile);
    }
}
