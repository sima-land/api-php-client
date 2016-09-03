<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Сущность страна.
 */
class CountryList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'country';
    }
}
