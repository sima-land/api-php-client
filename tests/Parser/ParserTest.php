<?php

namespace SimaLand\API\Tests\Parser;

use GuzzleHttp\Psr7\Response;
use SimaLand\API\Entities\CategoryList;
use SimaLand\API\Entities\CountryList;
use SimaLand\API\Entities\ItemList;
use SimaLand\API\Parser\Json;
use SimaLand\API\Parser\Parser;
use SimaLand\API\Tests\BaseCase;

class ParserTest extends BaseCase
{
    private function getMetaFilename()
    {
        return TEST_DIR . 'output' . DIRECTORY_SEPARATOR . "parser_meta";
    }

    public function testInvalidConstruct()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Param \"metaFilename\" can`t be empty");
        new Parser();
    }

    public function testRun()
    {
        $this->setResponse(require(TEST_DIR . "/data/item.php"));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $this->setResponse(require(TEST_DIR . "/data/category.php"));
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));
        $expectedItem = TEST_DIR . 'data/item.txt';
        $expectedCategory = TEST_DIR . 'data/category.txt';
        $actualItem = TEST_DIR . 'output/item.txt';
        $actualCategory = TEST_DIR . 'output/category.txt';
        @unlink($actualItem);
        @unlink($actualCategory);
        @unlink($this->getMetaFilename());

        $client = $this->getClient();

        $itemList = new ItemList($client, ['logger' => $this->getLogger()]);
        $itemList->countThreads = 1;
        $itemStorage = new Json(['filename' => TEST_DIR . 'output/item.txt']);

        $categoryList = new CategoryList($client, ['logger' => $this->getLogger()]);
        $categoryList->countThreads = 1;
        $categoryStorage = new Json(['filename' => TEST_DIR . 'output/category.txt']);

        $parser = new Parser(['metaFilename' => $this->getMetaFilename(), 'logger' => $this->getLogger()]);
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
        $expectedItem = TEST_DIR . 'data/item.txt';
        $actualItem = TEST_DIR . 'output/item.txt';
        @unlink($actualItem);
        @unlink($this->getMetaFilename());

        // формируем список товаров для 2х запросов
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

        // эмулируем 500 ошибку на сервере и падение парсера

        $itemList = new ItemList($client, ['logger' => $this->getLogger()]);
        $itemList->countThreads = 1;
        $itemList->repeatCount = 0;
        $itemStorage = new Json(['filename' => $actualItem]);

        $parser = new Parser([
            'metaFilename' => $this->getMetaFilename(),
            'iterationCount' => 2,
            'logger' => $this->getLogger(),
        ]);
        $parser->addEntity($itemList, $itemStorage);

        try {
            $parser->run();
            $this->fail('The parser should generate an error');
        } catch (\Exception $e) {
        }

        // возобновление парсинга после сбоя на сервере

        $itemList = new ItemList($client, ['logger' => $this->getLogger()]);
        $itemList->countThreads = 1;
        $itemList->repeatCount = 0;
        $itemStorage = new Json(['filename' => $actualItem]);

        $parser = new Parser(['metaFilename' => $this->getMetaFilename(), 'logger' => $this->getLogger()]);
        $parser->addEntity($itemList, $itemStorage);
        $parser->run();
        $this->assertEquals($this->getFileData($expectedItem), $this->getFileData($actualItem));

        $metaData = file_get_contents($this->getMetaFilename());
        $metaData = json_decode($metaData, true);
        $this->assertNotEmpty($metaData['item']['id-greater-than']);

        $parser->reset();
        $this->assertFileDoesNotExist($this->getMetaFilename());

        @unlink($actualItem);
    }

    public function testSaveMetaPage()
    {
        @unlink($this->getMetaFilename());
        $actualFile = TEST_DIR . 'output/country.txt';
        $body = require(TEST_DIR . "data/country.php");
        $this->setResponse($body);
        $body['_meta']['currentPage'] = 2;
        $this->setResponse($body);
        $this->setGuzzleHttpResponse(new Response(404, [], 'Not Found'));

        $client = $this->getClient();
        $countryList = new CountryList($client, ['countThreads' => 1, 'logger' => $this->getLogger()]);
        $countryStorage = new Json(['filename' => $actualFile]);

        $parser = new Parser(['metaFilename' => $this->getMetaFilename(), 'logger' => $this->getLogger()]);
        $parser->addEntity($countryList, $countryStorage);
        $parser->run();

        $metaData = file_get_contents($this->getMetaFilename());
        $metaData = json_decode($metaData, true);
        $this->assertEquals(2, $metaData['country']['page']);
        $parser->reset();

        @unlink($actualFile);
    }

    public function testFinishParseEntity()
    {
        @unlink($this->getMetaFilename());
        $actualFile = TEST_DIR . 'output/category.txt';
        $body = require(TEST_DIR . "/data/category.php");
        $body['items'] = [];

        // задаем только один ответ
        $this->setResponse($body);

        $client = $this->getClient();
        $categoryList = new CategoryList($client, ['countThreads' => 1, 'logger' => $this->getLogger()]);
        $categoryStorage = new Json(['filename' => $actualFile]);

        $parser = new Parser(['metaFilename' => $this->getMetaFilename(), 'logger' => $this->getLogger()]);
        $parser->addEntity($categoryList, $categoryStorage);
        $parser->run();

        $metaData = file_get_contents($this->getMetaFilename());
        $metaData = json_decode($metaData, true);
        $this->assertTrue($metaData['category']['finish']);

        // проверяем что повторый запуск парсинга не отрабатывает

        $parser = new Parser(['metaFilename' => $this->getMetaFilename(), 'logger' => $this->getLogger()]);
        $parser->addEntity($categoryList, $categoryStorage);
        $parser->run();

        $parser->reset();

        @unlink($actualFile);
    }

    /**
     * @param $filename
     * @return array
     */
    private function getFileData($filename)
    {
        $fh = fopen($filename, "r");
        $data = [];
        while (!feof($fh)) {
            $rawLine = fgets($fh);
            $line = json_decode(strip_tags($rawLine), true);
            if ($line) {
                $key = $line['id'];
                if (isset($data[$line["id"]])) {
                    $key = $line["id"] . "_" . uniqid();
                }
                $data[$key] = $line;
            }
        }
        fclose($fh);
        return $data;
    }
}
