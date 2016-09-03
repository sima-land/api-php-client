<?php

namespace SimaLand\API\Parser;

use SimaLand\API\Record;

/**
 * Интерфейс для сохранений данных.
 */
interface StorageInterface
{
    /**
     * Сохранить строку сущности.
     *
     * @param mixed $item
     */
    public function save(Record $item);
}
