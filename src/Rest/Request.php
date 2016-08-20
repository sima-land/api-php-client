<?php

namespace SimaLand\API\Rest;

use SimaLand\API\Object;

/**
 * Request for api simaland.
 */
class Request extends Object
{
    /**
     * @var string
     */
    public $entity;

    /**
     * @var string
     */
    public $method = 'GET';

    /**
     * @var array|null
     */
    public $getParams;

    /**
     * @var array|null
     */
    public $postParams;
}
