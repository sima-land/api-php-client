<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Сущности торговые мароки.
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
