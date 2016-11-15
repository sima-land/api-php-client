[![Build Status](https://travis-ci.org/sima-land/api-php-client.svg?branch=master)](https://travis-ci.org/sima-land/api-php-client)
[![StyleCI](https://styleci.io/repos/65816741/shield)](https://styleci.io/repos/65816741)
[![Code Coverage](https://scrutinizer-ci.com/g/sima-land/api-php-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/sima-land/api-php-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sima-land/api-php-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/sima-land/api-php-client/?branch=master)

# Библиотека для работы с API www.sima-land.ru

Библиотека для получения каталога интернет-магазина [www.sima-land.ru](https://www.sima-land.ru/api/v3/help/).
Предназначена для разработчиков которые хотят максимально быстро
подключить каталог [www.sima-land.ru](https://www.sima-land.ru/api/v3/help/) к себе на сайт и 
не является готовым решением которое можно самостоятельно, без привлечения порограммиста, 
подключить к готовому интернет магазину.

Версия библиотеки совпадает с версией API которую она использует. В настоящий момент
актуальной версией API является [API v3](https://www.sima-land.ru/api/v3/help/)

Библиотека состоит из двух основных компонентов клиента и парсера:

- клиент позволяет делать произвольные запросы к API sima-land.ru, формирует все необходимые для этого заголовки.
- парсер использует клиент и позволяет загрузить данные и сохранить их в указанное место.

## Содержание

- [Установка и требования](doc/requirements.md)
- [Клиент](doc/client.md)
- [Парсер](doc/parser.md)
- [Примеры](doc/example.md)
- [Логирование](doc/logger.md)
- [FAQ](doc/FAQ.md)

## Документация API

* [https://www.sima-land.ru/api/v3/help/](https://www.sima-land.ru/api/v3/help/)

## Ограничение

Существует лимит в 250 запросов к API за 10 секунд.

## Тесты

Тесты запускаются из корневой директории пакета.

```sh
php ./vendor/bin/phpunit
```

## Если что-то пошло не так

Вы можете задать вопрос в [issue](https://github.com/sima-land/api-php-client/issues)

