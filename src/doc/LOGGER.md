# Логирование

Каждый компонент логирует свои действия.
По умолчанию используется стандартный логгер который выводит лог в `php://output`.
Этот логгер вы можете переопределить, он должен реализовать интерфейс `\Psr\Log\LoggerInterface`.

```php
$logger = new \Monolog\Logger(Object::LOGGER_NAME, [new \Monolog\Handler\NullHandler()]);

$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password',
    'logger' => $logger,
]);

// или

$client->setLogger($logger);
```
