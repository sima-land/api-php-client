<?php

require_once "vendor/autoload.php";

// Путь до директории с файлами.
$pathData = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR;

// Если нет директории, создаем ее.
if (!file_exists($pathData)) {
    mkdir($pathData);
    chmod($pathData, 0777);
}

// Создаем API клиент. Обязательно указать логин и пароль.
// Клиент при первом обращении получает токен, который сохраняет по указанному пути.
$client = new \SimaLand\API\Rest\Client([
    'login' => 'login',
    'password' => 'password',

    // Необязательные параметры.

    // Путь до токена.
    'pathToken' => $pathData,
    // Базовый url API sima-land.ru.
    'baseUrl' => 'https://www.sima-land.ru/api/v3',
]);

// Добавляем для работы клиента HTTP клиент.
// HTTP клиент должен реализовать интерфейс \GuzzleHttp\ClientInterface.
$httpClient = new \GuzzleHttp\Client();
$client->setHttpClient($httpClient);

// Создаем объект логгер. Для логирование работы парсера.
// Логгер должен реализовать интерфейс \Psr\Log\LoggerInterface.
// По умолчанию весь лог отправляется в php://output.
$logger = new \Monolog\Logger('SimaParser');
$logger->pushHandler(new \Monolog\Handler\StreamHandler($pathData . "parser.log"));
// Логгер можно добавить с помощью метода setLogger
// или передать параметром в конструктор API клиента new Client(['logger' => $logger]).
$client->setLogger($logger);

// Создаем объекты сущностей и место хронения данных.

// Категории.
// Передадим логгер этому объекту в конструкторе.
// Парсер может одновременно обращаться к API в несколько потоков.
// Существует лимит, 250 запросов к API за 10 секунд.
$categoryList = new \SimaLand\API\Entities\CategoryList(
    $client,
    [
        'logger' => $logger,

        // Необязательные свойства.

        // Установим 10 потоков. По умолчанию парсер работает в 5 потоков.
        'countThreads' => 10,
        // Можем добавить GET параметры к запросу API.
        // Например, будем получать категории, включая категории 18+.
        'getParams' => [
            'with_adult' => 1,
        ],
        // Количество повторов обращения к ресурсу при ошибках.
        // По умолчанию 30.
        'repeatTimeout' => 20,
        // Время в секундах до следующего обращения к ресурсу.
        // По умолчанию 30.
        'repeatCount' => 20,
    ]
);

// Кроме того GET параметры могут быть добавлены методом addGetParams().
$categoryList->addGetParams([
    // Запросим все активные категории (содержащие активные товары либо в себе, либо в своих потомках).
    "is_not_empty" => 1
]);


/*
// Этот метод вернет набор данных сущности.
$responses = $categoryList->get();
foreach ($responses as $response) {
    if ($response->getStatusCode() == 200) {
        $body = json_decode($response->getBody(), true);
        // Метод getCollectionKey() Вернет ключ по которому находятся все записи сущности.
        $collectionKey = $categoryList->getCollectionKey();
        // Метод getMetaKey() Вернет ключ по которому находятся мата данные запроса.
        $metaKey = $categoryList->getMetaKey();
        foreach ($body[$collectionKey] as $item) {
            // Ваш код.
        }
    } else {
        throw new \Exception($response->getReasonPhrase(), $responses->getStatusCode());
    }
}

// Каждая сущность реализует интерфейс \Iterator. Соответственно можно получить все данные сущности следующим образом.
foreach ($categoryList as $record) {
    // $record объект класса \SimaLand\API\Record.
    // Здесь вы можете реализовать сохранение данных.
}
*/

// Хранение данных атрибутов
// Вы можете реализовать свой класс хранения данных, который будет сохранять в MySQL, PostgresQL и т. п..
// Этот класс должен реализовать интерфейс \SimaLand\API\Parser\StorageInterface.
// Сейчас мы данные этой сущности сохраним в Json файл.
$categoryStorage = new \SimaLand\API\Parser\Json([
    'filename' => $pathData . 'category.txt'
]);

// Атрибуты товаров.
$attrList = new \SimaLand\API\Entities\AttrList(
    $client,
    [
        'logger' => $logger,
        // Ключ, который отвечает за номер потока. По дефолту "page".
        // За исключением сущности "Категории" и "Товар", там ключ будет "id-mf".
        'keyThreads' => 'page',
    ]
);
$attrStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'attr.txt']);

// Опция атрибута товара.
$optionList = new \SimaLand\API\Entities\OptionList($client, ['logger' => $logger]);
$optionStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'option.txt']);

// Тип значения атрибута товара.
$dataTypeList = new \SimaLand\API\Entities\DatatypeList($client, ['logger' => $logger]);
$dataTypeStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'datatype.txt']);

// Материалы.
$materialList = new \SimaLand\API\Entities\MaterialList($client, ['logger' => $logger]);
$materialStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'material.txt']);

// Серии товаров.
$seriesList = new \SimaLand\API\Entities\SeriesList($client, ['logger' => $logger]);
$seriesStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'series.txt']);

// Страны.
$countryList = new \SimaLand\API\Entities\CountryList($client, ['logger' => $logger]);
$countryStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'country.txt']);

// Торговые марки.
$trademarkList = new \SimaLand\API\Entities\TrademarkList($client, ['logger' => $logger]);
$trademarkStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'trademark.txt']);

// Товары.
$itemList = new \SimaLand\API\Entities\ItemList(
    $client,
    [
        'logger' => $logger,
        // Получим все товары, включая 18+.
        'getParams' => [
            'with_adult' => 1
        ],
    ]
);
$itemStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'item.txt']);

// Связь атрибута с товаром.
$attrItemList = new \SimaLand\API\Entities\AttrItemList($client, ['logger' => $logger]);
$attrItemStorage = new \SimaLand\API\Parser\Json(['filename' => $pathData . 'attr_item.txt']);

// Загрузка и сохранение всех записей сущностей.
$parser = new \SimaLand\API\Parser\Parser([
    // Путь до файла с метаданными. Он необходим для продолжения парсинга, если по какой-то причине парсер остановил свою работы.
    'metaFilename' => $pathData . 'parser_meta',

    // Необязательные свойства.

    // Кол-во итераций после которых сохраняются метаданные.
    'iterationCount' => 1000,
    // Добавим логгер.
    'logger' => $logger
]);

// Добавим в парсер сущности.
// Метод addEntity() принимает два параметра: список и хранилище.
// Лист должен наследоваться от класса \SimaLand\API\AbstractList.
// Хранилище должно реализовывать интерфейс \SimaLand\API\Parser\StorageInterface.
$parser->addEntity($categoryList, $categoryStorage);
$parser->addEntity($attrList, $attrStorage);
$parser->addEntity($optionList, $optionStorage);
$parser->addEntity($dataTypeList, $dataTypeStorage);
$parser->addEntity($materialList, $materialStorage);
$parser->addEntity($seriesList, $seriesStorage);
$parser->addEntity($countryList, $countryStorage);
$parser->addEntity($trademarkList, $trademarkStorage);
$parser->addEntity($itemList, $itemStorage);
$parser->addEntity($attrItemList, $attrItemStorage);

// Этот метод удалит метаданные, чтоб начать парсинг с самого начала.
// $parser->reset();

// Вы можете запустить парсинг с параметром false. В этом случаи метаданные будут игнорироваться.
// $parser->run(false);

// Запускаем процесс парсинга.
$parser->run();
