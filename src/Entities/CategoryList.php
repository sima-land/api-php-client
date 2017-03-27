<?php

namespace SimaLand\API\Entities;

use SimaLand\API\AbstractList;
use SimaLand\API\Rest\Client;
use SimaLand\API\Record;
use SimaLand\API\Rest\Request;

/**
 * Категории.
 */
class CategoryList extends AbstractList
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
    public function __construct(Client $client, array $options = [])
    {
        parent::__construct($client, $options);
    }

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
        $request->getParams[$this->keyAlternativePagination] = $lastId;
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

    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function setGetParams(array $value)
    {
        if (!isset($value[$this->keyAlternativePagination])) {
            $value[$this->keyAlternativePagination] = 0;
        }
        parent::setGetParams($value);
    }
}
