[![Build Status](https://travis-ci.org/sima-land/api-php-client.svg?branch=master)](https://travis-ci.org/sima-land/api-php-client)
[![StyleCI](https://styleci.io/repos/65816741/shield)](https://styleci.io/repos/65816741)

# api-php-client

Клиент для работы с API сайта sima-land.ru

Клиент позволяет выкачивать каталог товаров с сайта sima-land.ru. 

## Требования ##

* [PHP 5.5 или новее](http://www.php.net/)
* [composer](https://getcomposer.org/download/)

## Установка ##

```sh
composer require "simaland/api-php-client": "0.1"
```

## Документация API ##

* [https://www.sima-land.ru/api/v3/help/](https://www.sima-land.ru/api/v3/help/)

## API клиент ##

Клиент позволяет делать произвольные запросы к API sima-land.ru, формирует все необходимые для этого заголовки.
Клиент умеет делать одновременно несколько асинхронных запросов.

### Авторизация ###

Авторизация идет по [JWT токену](https://tools.ietf.org/html/rfc7519).
При первом запросе к ресурсу, идет авторизация по логину и паролю для получения токена.
Токен временно сохраняется  в файле. Путь до файла вы можете задать свой, передав в конструктор клиента `\SimaLand\API\Rest\Client` переменную `pathToken`.
Время жизни токена 7 суток.

### Запрос к API ###

```php
$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password'
]);
$response = $client->get('category', ['page' => 5]);
$body = json_decode($response->getBody(), true);
foreach ($body['items'] as $item) {
    // you code
}
```

### Асинхронные запросы к API ###

```php
$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password'
]);
$requestPage1 = new \SimaLand\API\Rest\Request([
    'entity' => $this->getEntity()
]);
$requestPage2 = new \SimaLand\API\Rest\Request([
    'entity' => $this->getEntity()
    'getParams' => ['page' => 2]
]);
$responses = $client->batchQuery([$requestPage1, $requestPage2]);
foreach ($responses as $response) {
    if ($responses->getStatusCode() == 200) {
        $body = json_decode($response->getBody(), true);
        foreach ($body['items'] as $item) {
            // you code
        }
    } else {
        throw new \Exception($response->getReasonPhrase(), $responses->getStatusCode());
    }
}
```

## Ограничение ##

Существует лимит, 250 запросов к API за 10 секунд.

## Парсер ###

Парсер позволит загрузить все данные сущности и сохранить их в указанное место.
Каждая сущность может делать одновременно несколько асинхронных запросов к API, что сократит время ожидания постраничной загрузки.

В метод `addEntity` передается два объекта.
Объект сущности и объект хранилища, который сохраняет данные.

Объект хранилища должен реализовать интерфейс `\SimaLand\API\Parser\StorageInterface`

### Пример использования парсера ### 

```php
$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password'
]);
$itemList = new \SimaLand\API\Entities\ItemList($client);
$itemStorage = new \SimaLand\API\Parser\Csv(['filename' => 'path/to/item.csv']);
$categoryList = new \SimaLand\API\Entities\CategoryList($client);
$categoryStorage = new \SimaLand\API\Parser\Csv(['filename' => 'path/to/category.csv']);
$parser = new \SimaLand\API\Parser\Parser();
$parser->addEntity($itemList, $itemStorage);
$parser->addEntity($categoryList, $categoryStorage);
$parser->run();
```
## Тесты ##

Тесты запускаются из корневой директории пакета.

```sh
php ./vendor/bin/phpunit
```
