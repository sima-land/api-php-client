<?php

namespace SimaLand\API;

/**
 * Запись сущности.
 */
class Record extends BaseObject
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
