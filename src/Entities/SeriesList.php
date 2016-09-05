<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Серии.
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
