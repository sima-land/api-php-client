<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Атрибут товара.
 */
class AttrList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'attr';
    }
}
