<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Связь атрибута с товаром.
 */
class AttrItemList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'attr-item';
    }
}
