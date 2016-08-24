<?php

namespace SimaLand\API\Parser;

use SimaLand\API\AbstractList;

/**
 * Load and save all records of entities.
 *
 * ```php
 *
 * $client = new \SimaLand\API\Rest\Client([
 *     'login' => 'login',
 *     'password' => 'password'
 * ]);
 * $itemList = new \SimaLand\API\Entities\ItemList($client);
 * $categoryList = new \SimaLand\API\Entities\CategoryList($client);
 * $storage = new Csv(['path' => 'path/to/dir']);
 * $parser = new Parser($storage);
 * $parser->setEntities([$itemList, $categoryList]);
 * $parser->run();
 *
 * ```
 */
class Parser
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var AbstractList[]
     */
    private $entities = [];

    /**
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param AbstractList $entity
     * @return Parser
     */
    public function addEntity(AbstractList $entity)
    {
        $this->entities[] = $entity;
        return $this;
    }

    /**
     * @param AbstractList[] $entities
     * @return Parser
     */
    public function setEntities(array $entities)
    {
        $this->entities = [];
        foreach ($entities as $entity) {
            $this->addEntity($entity);
        }
        return $this;
    }

    /**
     * Load and save all records of entities.
     */
    public function run()
    {
        foreach ($this->entities as $entity) {
            $this->storage->setEntity($entity->getEntity());
            foreach ($entity as $item) {
                $this->storage->save($item);
            }
        }
    }
}
