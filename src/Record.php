<?php

namespace SimaLand\API;

/**
 * Запись сущности.
 */
class Record extends Object
{
    /**
     * Данные одной строки.
     *
     * @var mixed
     */
    public $data;

    /**
     * Мета данные запроса к API.
     *
     * @var mixed
     */
    public $meta;
}
