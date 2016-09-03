<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Сущность материал.
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
