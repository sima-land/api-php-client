<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Связь атрибута с товаром.
 */
class AttrItemList extends AbstractList
{
    /**
     * GET параметр отвечающий за поток.
     *
     * @var string
     */
    public $keyThreads = 'id-mf';

    /**
     * Использовать альтернативную пагинацию.
     *
     * @var bool
     */
    public $useAlternativePagination = true;

    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'attr-item';
    }
}
