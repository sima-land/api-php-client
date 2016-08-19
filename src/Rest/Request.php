<?php

namespace SimaLand\API\Rest;

use SimaLand\API\Object;

class Request extends Object
{
    public $entity;

    public $method = 'GET';

    public $getParams;

    public $postParams;
}
