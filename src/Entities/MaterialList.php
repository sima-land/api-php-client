<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Материалы.
 */
class MaterialList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'material';
    }
}
