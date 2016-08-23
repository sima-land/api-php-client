<?php

namespace SimaLand\API;

/**
 * Base class.
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
