# api-php-client

Пример клиента для работы с API сайта sima-land.ru

Клиент позволяет выкачивать каталог товаров с сайта sima-land.ru. 

Пример использование
--------------------

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
