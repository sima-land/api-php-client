<?php

namespace SimaLand\API\Rest;

use SimaLand\API\Object;

/**
 * Класс запроса к API simaland.
 */
class Request extends Object
{
    /**
     * Наименование сущности.
     *
     * @var string
     */
    public $entity;

    /**
     * Метод запроса.
     *
     * @var string
     */
    public $method = 'GET';

    /**
     * GET параметры запроса.
     *
     * @var array|null
     */
    public $getParams;

    /**
     * POST параметры запроса.
     *
     * @var array|null
     */
    public $postParams;
}
