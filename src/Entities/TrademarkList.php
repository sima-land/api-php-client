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
    function getEntity()
    {
        return 'trademark';
    }
}
