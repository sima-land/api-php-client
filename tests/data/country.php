<?php
return [
    'items' => [
        [
            'id' => 1,
            'name' => 'Россия',
            'full_name' => 'Российская Федерация',
            'alpha2' => 'RU',
        ],
        [
            'id' => 2,
            'name' => 'Катар',
            'full_name' => 'Государство Катар',
            'alpha2' => 'QA',
        ],
        [
            'id' => 3,
            'name' => 'Сейшелы',
            'full_name' => 'Республика Сейшелы',
            'alpha2' => 'SC',
        ],
        [
            'id' => 4,
            'name' => 'Коста-Рика',
            'full_name' => 'Республика Коста-Рика',
            'alpha2' => 'CR',
        ],
        [
            'id' => 5,
            'name' => 'Кипр',
            'full_name' => 'Республика Кипр',
            'alpha2' => 'CY',
        ],
    ],
    '_links' =>
        [
            'self' =>
                [
                    'href' => 'https://www.sima-land.ru/api/v3/country/?page=1',
                ],
            'next' =>
                [
                    'href' => 'https://www.sima-land.ru/api/v3/country/?page=2',
                ],
            'last' =>
                [
                    'href' => 'https://www.sima-land.ru/api/v3/country/?page=896',
                ],
        ],
    '_meta' =>
        [
            'totalCount' => 252,
            'pageCount' => 6,
            'currentPage' => 1,
            'perPage' => 50,
        ],
];
