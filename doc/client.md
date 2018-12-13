# API Клиент

Клиент позволяет делать произвольные запросы к API sima-land.ru, формирует все необходимые для этого заголовки.
Умеет делать одновременно несколько асинхронных запросов.

## Авторизация

Авторизация идет по [JWT токену](https://tools.ietf.org/html/rfc7519). Для этого вам необходимо зарегистрироваться на сайте
[https://www.sima-land.ru/](https://www.sima-land.ru/) и использовать этот логин и пароль в клиенте.
При первом запросе к ресурсу, идет авторизация по логину и паролю для получения токена.
Токен временно сохраняется  в файле. Путь до файла вы можете задать свой, передав в конструктор клиента `\SimaLand\API\Rest\Client` переменную `tokenPath`.
Время жизни токена 7 суток.

## Запрос к API

```php
$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password'
]);
$response = $client->get('category', ['page' => 5]);
$body = json_decode($response->getBody(), true);
foreach ($body['items'] as $item) {
    // your code
}
```

## Асинхронные запросы к API

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
            // your code
        }
    } else {
        throw new \Exception($response->getReasonPhrase(), $responses->getStatusCode());
    }
}
```
