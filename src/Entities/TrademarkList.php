<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Класс сущности торговых марок.
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
