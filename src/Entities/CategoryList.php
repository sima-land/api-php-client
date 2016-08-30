<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Класс сущности категории.
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
