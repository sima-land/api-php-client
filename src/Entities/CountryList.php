<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Страны.
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
