<?php

namespace SimaLand\API\Parser;

/**
 * Интерфейс для сохранений данных.
 */
interface StorageInterface
{
    /**
     * @param mixed $item
     */
    public function save($item);
}
