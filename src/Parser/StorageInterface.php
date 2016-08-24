<?php

namespace SimaLand\API\Parser;

interface StorageInterface
{
    /**
     * @param mixed $item
     */
    public function save($item);

    /**
     * @param string $entity
     */
    public function setEntity($entity);
}
