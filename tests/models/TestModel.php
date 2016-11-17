<?php

namespace SimaLand\API\Tests\models;

use SimaLand\API\Object;

/**
 * Class TestModel
 * @package SimaLand\API\Tests\models
 *
 * @property $sid int
 * @property $name string
 */
class TestModel extends Object
{
    protected $sid;

    private $name;

    public function setName($name)
    {
        $this->name = "Test_{$name}";
    }

    public function getName()
    {
        return $this->name;
    }
}