# Примеры парсинга

## Получение товара и категорий

В данном примере парсинг получит все категории и товары.

```php
$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password'
]);

$parser = new \SimaLand\API\Parser\Parser(['metaFilename' => 'path/to/file']);

// добавляем список категорий
$categoryStorage = new \SimaLand\API\Parser\Json(['filename' => 'path/to/category.txt']);
$categoryList = new \SimaLand\API\Entities\CategoryList($client);
$parser->addEntity($categoryList, $categoryStorage);

// добавляем список товаров
$itemStorage = new \SimaLand\API\Parser\Json(['filename' => 'path/to/item.txt']);
$itemList = new \SimaLand\API\Entities\ItemList($client);
$parser->addEntity($itemList, $itemStorage);

$parser->run();
```


## Демонстрационное приложение

Пример приложения, позволяещего полностью скачать каталог.
Данный пример содержит подробное описание использования всех компонентов.

[Исходный код](../parser_example.php)