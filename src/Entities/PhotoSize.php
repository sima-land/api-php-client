<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;

/**
 * Размеры фотографий.
 */
class PhotoSize extends AbstractList
{
    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'photo-size';
    }
}
