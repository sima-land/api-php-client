<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Опция атрибута товара.
 */
class OptionList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'option';
    }
}
