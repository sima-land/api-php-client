<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

class CategoryList extends AbstractList
{
    /**
     * @inheritdoc
     */
    function getEntity()
    {
        return 'category';
    }
}
