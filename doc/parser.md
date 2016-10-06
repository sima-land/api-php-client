# Парсер

Парсер позволит загрузить все данные и сохранить их в указанное место.
Может делать одновременно несколько асинхронных запросов к API, что сократит время ожидания постраничной загрузки.

Для возобновления работы парсера, если что-то пошло не так, в конструктор нужно передать параметер `metaFilename`. Который
представляет собой полный путь до файла.

Если вы хотите заново загрузить данные, достаточно вызвать метод `reset()` перед методом `run()`.
В случае вызова `run(false)` парсер проигнорирует текущую позицию.

Объект хранилища должен реализовать интерфейс `\SimaLand\API\Parser\StorageInterface`

## Пример использования парсера

Выкачивание данных каталога

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

Возобновление после сбоя (сетевые проблемы, ошибки сервера и т.п.)

```php
// забываем текущую позицию и начинаем парсинг заново
$parser->reset();
$parser->run();

// игнорируем текущую позицию и начинаем парсинг заново
$parser->run(false);
```
