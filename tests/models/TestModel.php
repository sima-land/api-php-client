<?php

namespace SimaLand\API\Tests\models;

use SimaLand\API\BaseObject;

/**
 * Class TestModel
 * @package SimaLand\API\Tests\models
 *
 * @property $sid int
 * @property $name string
 */
class TestModel extends BaseObject
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
