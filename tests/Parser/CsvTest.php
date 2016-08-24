<?php

namespace SimaLand\API\Tests\Parser;

use SimaLand\API\Parser\Csv;
use SimaLand\API\Tests\BaseCase;

class CsvTest extends BaseCase
{
    /**
     * @expectedException \Exception
     */
    public function testConstruct()
    {
        new Csv();
    }

    public function testSave()
    {
        $item = require(TEST_DIR . "/data/item.php");
        $expectedFile = TEST_DIR . 'data/item.csv';
        $actualFile = TEST_DIR . 'output/item.csv';
        $storage = new Csv(['path' => TEST_DIR . 'output']);
        $storage->setEntity('item');
        foreach ($item['items'] as $item) {
            $storage->save($item);
        }
        $storage->close();
        $this->assertEquals(md5_file($expectedFile), md5_file($actualFile));
        @unlink($actualFile);
    }
}
