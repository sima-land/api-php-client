<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;
use SimaLand\API\Record;
use SimaLand\API\Rest\Request;

/**
 * Товары.
 */
class ItemList extends AbstractList
{
    /**
     * GET параметр отвечающий за поток.
     *
     * @var string
     */
    public $keyThreads = 'id-mf';

    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'item';
    }

    /**
     * GET параметры запроса.
     *
     * @var array
     */
    public $getParams = [
        'id-greater-than' => 0
    ];

    /**
     * @inheritdoc
     */
    public function assignPage(Request &$request, Record $record = null)
    {
        $lastId = 0;
        if ($record && $record->data) {
            $lastId = (int)$record->data['id'];
        }
        if (!is_array($request->getParams)) {
            $request->getParams = (array)$request->getParams;
        }
        if ($lastId > 0) {
            $request->getParams[$this->keyAlternativePagination] = $lastId;
        }
    }

    /**
     * @inheritdoc
     */
    public function assignThreadsNumber(Request &$request, $number = 0)
    {
        if (!is_array($request->getParams)) {
            $request->getParams = (array)$request->getParams;
        }
        $request->getParams[$this->keyThreads] = "{$this->countThreads},$number";
    }
}
