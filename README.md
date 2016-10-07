[![Build Status](https://travis-ci.org/sima-land/api-php-client.svg?branch=master)](https://travis-ci.org/sima-land/api-php-client)
[![StyleCI](https://styleci.io/repos/65816741/shield)](https://styleci.io/repos/65816741)
[![Code Coverage](https://scrutinizer-ci.com/g/sima-land/api-php-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sima-land/api-php-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sima-land/api-php-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sima-land/api-php-client/?branch=master)

# api-php-client

Клиент для работы с API сайта sima-land.ru Позволяет выкачивать каталог товаров с сайта sima-land.ru.

## Требования

* OS Linux или MacOS
* [PHP 5.5 или новее](http://www.php.net/)
* [composer](https://getcomposer.org/download/)
* curl 

## Установка

```sh
composer require "sima-land/api-php-client": "~1"
```

## Документация API

* [https://www.sima-land.ru/api/v3/help/](https://www.sima-land.ru/api/v3/help/)

## Возможности
* Формирование HTTP запросов, авторизация
* Асинхронные запросы на получение данных
* Получение каталога
* Возобновление скачивания данных после сбоя
* Повторное опрашивание ресурса при возникновении ошибки
* Логирование

## Пример парсинга

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

Подробное описание компонентов парсера можете посмотреть здесь:

* [Клиент api sima-land.ru](doc/client.md)
* [Парсер](doc/parser.md)
* [Логирование](doc/logger.md)
* [Часто задаваемые вопросы](doc/faq.md)

## Демонстрационное приложение
Пример приложения, позволяещего полностью скачать каталог.
Данный пример содержит подробное описание использования всех компонентов.

[Исходный код](parser_example.php)

# Ограничение

Существует лимит в 250 запросов к API за 10 секунд.

## Тесты

Тесты запускаются из корневой директории пакета.

```sh
php ./vendor/bin/phpunit
```

## Если что-то пошло не так
Вы можете задать вопрос в [issue](https://github.com/sima-land/api-php-client/issues)

