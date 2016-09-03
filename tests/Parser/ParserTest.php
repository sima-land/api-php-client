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
    private function getMetaFilename()
    {
        return TEST_DIR . 'output' . DIRECTORY_SEPARATOR . "parser_meta";
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Param "metaFilename" can`t be empty
     */
    public function testInvalidConstruct()
    {
        new Parser();
    }

    public function testRun()
    {
        $this->setResponse(require(TEST_DIR . "/data/item.php"));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $this->setResponse(require(TEST_DIR . "/data/category.php"));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $expectedItem = TEST_DIR . 'data/item.csv';
        $expectedCategory = TEST_DIR . 'data/category.csv';
        $actualItem = TEST_DIR . 'output/item.csv';
        $actualCategory = TEST_DIR . 'output/category.csv';
        @unlink($actualItem);
        @unlink($actualCategory);
        @unlink($this->getMetaFilename());

        $client = $this->getClient();
        $itemList = new ItemList($client);
        $itemList->countThreads = 1;
        $itemStorage = new Csv(['filename' => TEST_DIR . 'output/item.csv']);
        $categoryList = new CategoryList($client);
        $categoryList->countThreads = 1;
        $categoryStorage = new Csv(['filename' => TEST_DIR . 'output/category.csv']);
        $parser = new Parser(['metaFilename' => $this->getMetaFilename()]);
        $parser->addEntity($itemList, $itemStorage);
        $parser->addEntity($categoryList, $categoryStorage);
        $parser->run();

        $this->assertEquals(md5_file($expectedItem), md5_file($actualItem));
        $this->assertEquals(md5_file($expectedCategory), md5_file($actualCategory));
        @unlink($actualItem);
        @unlink($actualCategory);
    }

    public function testRestoreParse()
    {
        $expectedItem = TEST_DIR . 'data/item.csv';
        $actualItem = TEST_DIR . 'output/item.csv';
        @unlink($actualItem);
        @unlink($this->getMetaFilename());
        $body1 = $body2 = require(TEST_DIR . "/data/item.php");
        $countItems = count($body1['items']);
        for ($i = 0; $i < $countItems; $i++) {
            if ($i % 2 == 0) {
                unset($body1['items'][$i]);
            } else {
                unset($body2['items'][$i]);
            }
        }

        $this->setResponse($body1);
        $this->setGuzzleHttpResponse(new Response(500, [], 'Internal Server Error'));
        $this->setResponse($body2);
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));

        $client = $this->getClient();
        $itemList = new ItemList($client);
        $itemList->countThreads = 1;
        $itemStorage = new Csv(['filename' => $actualItem]);
        $parser = new Parser([
            'metaFilename' => $this->getMetaFilename(),
            'countRecordsSave' => 2,
        ]);
        $parser->addEntity($itemList, $itemStorage);
        try {
            $parser->run();
            $this->fail('The parser should generate an error');
        } catch (\Exception $e) {
        }
        $itemList = new ItemList($client);
        $itemList->countThreads = 1;
        $itemStorage = new Csv(['filename' => $actualItem]);
        $parser = new Parser(['metaFilename' => $this->getMetaFilename()]);
        $parser->addEntity($itemList, $itemStorage);
        $parser->run();
        $this->assertEquals($this->getFileData($expectedItem), $this->getFileData($actualItem));

        $metaData = file_get_contents($this->getMetaFilename());
        $metaData = json_decode($metaData, true);
        $this->assertNotEmpty($metaData['item']['id-greater-than']);

        $parser->reset();
        $this->assertFileNotExists($this->getMetaFilename());

        @unlink($actualItem);
    }

    public function testSaveMetaPage()
    {
        @unlink($this->getMetaFilename());
        $actualFile= TEST_DIR . 'output/category.csv';
        $body = require(TEST_DIR . "/data/category.php");
        $this->setResponse($body);
        $body['_meta']['currentPage'] = 2;
        $this->setResponse($body);
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));

        $client = $this->getClient();
        $categoryList = new CategoryList($client, ['countThreads' => 1]);
        $categoryStorage = new Csv(['filename' => $actualFile]);
        $parser = new Parser(['metaFilename' => $this->getMetaFilename()]);
        $parser->addEntity($categoryList, $categoryStorage);
        $parser->run();


        $metaData = file_get_contents($this->getMetaFilename());
        $metaData = json_decode($metaData, true);
        $this->assertEquals(2, $metaData['category']['page']);
        $parser->reset();
        @unlink($actualFile);
    }

    public function testFinishParseEntity()
    {
        @unlink($this->getMetaFilename());
        $actualFile= TEST_DIR . 'output/category.csv';
        $body = require(TEST_DIR . "/data/category.php");
        $body['items'] = [];
        $this->setResponse($body);

        $client = $this->getClient();
        $categoryList = new CategoryList($client, ['countThreads' => 1]);
        $categoryStorage = new Csv(['filename' => $actualFile]);
        $parser = new Parser(['metaFilename' => $this->getMetaFilename()]);
        $parser->addEntity($categoryList, $categoryStorage);
        $parser->run();

        $metaData = file_get_contents($this->getMetaFilename());
        $metaData = json_decode($metaData, true);
        $this->assertTrue($metaData['category']['finish']);

        $parser = new Parser(['metaFilename' => $this->getMetaFilename()]);
        $parser->addEntity($categoryList, $categoryStorage);
        $parser->run();

        $parser->reset();

        @unlink($actualFile);
    }

    /**
     * @param $filename
     * @return array
     */
    private function getFileData($filename) {
        $fh = fopen($filename, "r");
        $data = [];
        while (!feof($fh)) {
            $line = fgetcsv($fh, null, ';');
            if ($line) {
                $key = $line[0];
                if (isset($data[$line[0]])) {
                    $key = $line[0] . "_" . uniqid();
                }
                $data[$key] = $line;
            }
        }
        fclose($fh);
        return $data;
    }
}
