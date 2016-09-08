<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Тип значения атрибута товара.
 */
class DatatypeList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'datatype';
    }
}
