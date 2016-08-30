<?php

namespace SimaLand\API\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Entities\CategoryList;
use SimaLand\API\Entities\ItemList;
use SimaLand\API\Parser\Csv;
use SimaLand\API\Parser\Parser;
use SimaLand\API\Tests\BaseCase;

class ParserTest extends BaseCase
{
    public function testRun()
    {
        $this->setResponse(require(TEST_DIR . "/data/item.php"));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $this->setResponse(require(TEST_DIR . "/data/category.php"));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $expectedItem = TEST_DIR . 'data/item.csv';
        $expectedCategory = TEST_DIR . 'data/category.csv';

        $client = $this->getClient();
        $itemList = new ItemList($client);
        $itemList->countThreads = 1;
        $itemStorage = new Csv(['filename' => TEST_DIR . 'output/item.csv']);
        $categoryList = new CategoryList($client);
        $categoryList->countThreads = 1;
        $categoryStorage = new Csv(['filename' => TEST_DIR . 'output/category.csv']);
        $parser = new Parser();
        $parser->addEntity($itemList, $itemStorage);
        $parser->addEntity($categoryList, $categoryStorage);
        $parser->run();

        $actualItem = TEST_DIR . 'output/item.csv';
        $actualCategory = TEST_DIR . 'output/category.csv';
        $this->assertEquals(md5_file($expectedItem), md5_file($actualItem));
        $this->assertEquals(md5_file($expectedCategory), md5_file($actualCategory));
        unlink($actualItem);
        unlink($actualCategory);
    }
}
