<?php
return [
    'items' => [
        [
            'id' => 1,
            'sid' => 1,
            'name' => 'Аксессуары для волос',
            'priority' => 2000,
            'priority_home' => 0,
            'priority_menu' => 2000,
        ],
        [
            'id' => 2,
            'sid' => 2,
            'name' => 'Бижутерия',
            'priority' => 1000,
            'priority_home' => 800,
            'priority_menu' => 1000,
        ],
        [
            'id' => 3,
            'sid' => 3,
            'name' => 'Бизнес-сувениры',
            'priority' => 4890,
            'priority_home' => 0,
            'priority_menu' => 4890,
        ],
        [
            'id' => 4,
            'sid' => 4,
            'name' => 'Брелоки и подвески',
            'priority' => 4100,
            'priority_home' => 0,
            'priority_menu' => 4100,
        ],
        [
            'id' => 5,
            'sid' => 5,
            'name' => 'Талисманы и фэншуй',
            'priority' => 3000,
            'priority_home' => 0,
            'priority_menu' => 3000,
        ],
    ],
    '_links' =>
        [
            'self' =>
                [
                    'href' => 'https://www.sima-land.ru/api/v3/category/?page=1',
                ],
            'next' =>
                [
                    'href' => 'https://www.sima-land.ru/api/v3/category/?page=2',
                ],
            'last' =>
                [
                    'href' => 'https://www.sima-land.ru/api/v3/category/?page=896',
                ],
        ],
    '_meta' =>
        [
            'totalCount' => 44752,
            'pageCount' => 896,
            'currentPage' => 1,
            'perPage' => 50,
        ],
];
