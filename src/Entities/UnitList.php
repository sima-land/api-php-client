<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Единицы измерения.
 */
class UnitList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'unit';
    }
}
