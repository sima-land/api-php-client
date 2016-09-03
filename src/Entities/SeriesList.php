<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Сущность серии товаров.
 */
class SeriesList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'series';
    }
}
