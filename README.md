[![Build Status](https://travis-ci.org/sima-land/api-php-client.svg?branch=master)](https://travis-ci.org/sima-land/api-php-client)

# api-php-client

Пример клиента для работы с API сайта sima-land.ru

Клиент позволяет выкачивать каталог товаров с сайта sima-land.ru. 

## Требования ##

* [PHP 5.5 или новее](http://www.php.net/)
* [composer](https://getcomposer.org/download/)

## Установка ##

```
composer require "simaland/api-php-client": "0.1"
```

## Документация ##

* [https://www.sima-land.ru/api/v3/help/](https://www.sima-land.ru/api/v3/help/)

## Пример использование ##

```php
$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password'
]);
$itemList = new \SimaLand\API\Entities\ItemList($client);
$categoryList = new \SimaLand\API\Entities\CategoryList($client);
$storage = new \SimaLand\API\Parser\Csv(['path' => 'path/to/dir']);
$parser = new \SimaLand\API\Parser\Parser($storage);
$parser->setEntities([$categoryList, $itemList]);
$parser->run();
```
