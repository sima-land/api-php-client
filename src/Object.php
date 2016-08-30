<?php

namespace SimaLand\API;

/**
 * Базовый класс.
 */
class Object
{
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
