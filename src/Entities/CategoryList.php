<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Категории.
 */
class CategoryList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'category';
    }
}
