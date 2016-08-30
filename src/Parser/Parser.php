<?php

namespace SimaLand\API\Parser;

use SimaLand\API\AbstractList;

/**
 * Загрузка и сохранение всех записей сущностей.
 *
 * ```php
 *
 * $client = new \SimaLand\API\Rest\Client([
 *     'login' => 'login',
 *     'password' => 'password'
 * ]);
 * $itemList = new \SimaLand\API\Entities\ItemList($client);
 * $itemStorage = new Csv(['filename' => 'path/to/item.csv']);
 * $categoryList = new \SimaLand\API\Entities\CategoryList($client);
 * $categoryStorage = new Csv(['filename' => 'path/to/category.csv']);
 * $parser = new Parser();
 * $parser->addEntity($itemList, $itemStorage);
 * $parser->addEntity($categoryList, $categoryStorage);
 * $parser->run();
 *
 * ```
 */
class Parser
{
    /**
     * @var array
     */
    private $list = [];

    /**
     * @param AbstractList $entity
     * @param StorageInterface $storage
     * @return Parser
     */
    public function addEntity(AbstractList $entity, StorageInterface $storage)
    {
        $this->list[] = [
            'entity' => $entity,
            'storage' => $storage
        ];
        return $this;
    }

    /**
     * Run parser
     */
    public function run()
    {
        foreach ($this->list as $el) {
            /** @var AbstractList $entity */
            $entity = $el['entity'];
            /** @var StorageInterface  $storage */
            $storage = $el['storage'];
            foreach ($entity as $item) {
                $storage->save($item);
            }
        }
    }
}
