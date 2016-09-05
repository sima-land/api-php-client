<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Торговые марки.
 */
class TrademarkList extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'trademark';
    }
}
