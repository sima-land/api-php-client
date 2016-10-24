<?php

namespace SimaLand\API\Parser;

use SimaLand\API\AbstractList;
use SimaLand\API\Object;
use SimaLand\API\Record;

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
 * $itemStorage = new Json(['filename' => 'path/to/item.txt']);
 * $categoryList = new \SimaLand\API\Entities\CategoryList($client);
 * $categoryStorage = new Json(['filename' => 'path/to/category.txt']);
 * $parser = new Parser(['metaFilename' => 'path/to/file']);
 * $parser->addEntity($itemList, $itemStorage);
 * $parser->addEntity($categoryList, $categoryStorage);
 * $parser->run();
 *
 * ```
 */
class Parser extends Object
{
    /**
     * Кол-во итераций после которых сохраняются мета данные.
     *
     * @var int
     */
    public $iterationCount = 1000;

    /**
     * @var array
     */
    protected $list = [];

    /**
     * Путь до файла с мета данными.
     *
     * @var string
     */
    protected $metaFilename;

    /**
     * Мета данные.
     *
     * @var array
     */
    protected $metaData = [];

    /**
     * @inheritdoc
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['metaFilename'])) {
            throw new \Exception('Param "metaFilename" can`t be empty');
        }
        $this->metaFilename = $options['metaFilename'];
        unset($options['metaFilename']);
        parent::__construct($options);
    }

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
     * Сбросить мета данные.
     *
     * @return Parser
     */
    public function reset()
    {
        if (file_exists($this->metaFilename)) {
            unlink($this->metaFilename);
        }
        return $this;
    }

    /**
     * Запустить парсер.
     *
     * @param bool|false $continue Продолжить парсить с место обрыва.
     */
    public function run($continue = true)
    {
        $logger = $this->getLogger();
        $this->loadMetaData();
        foreach ($this->list as $el) {
            /** @var AbstractList $entity */
            $entity = $el['entity'];
            $entityName = $entity->getEntity();
            if ($continue && isset($this->metaData[$entityName])) {
                if (isset($this->metaData[$entityName]['finish']) && $this->metaData[$entityName]['finish']) {
                    continue;
                }
                $entity->addGetParams($this->metaData[$entityName]);
            }
            /** @var StorageInterface $storage */
            $storage = $el['storage'];
            $logger->info("Parse \"{$entityName}\"");
            foreach ($entity as $key => $record) {
                if ($continue) {
                    $this->fillAndSaveMetaData($entity, $record, $key);
                }
                $storage->save($record);
            }
            $logger->info("Finish parse \"{$entityName}\"");
            if ($continue) {
                $this->finishParseEntity($entity);
                $this->saveMetaData();
            }
        }
    }

    /**
     * Загрузить мета данные.
     */
    protected function loadMetaData()
    {
        if (!file_exists($this->metaFilename)) {
            return;
        }
        $data = file_get_contents($this->metaFilename);
        $this->metaData = (array)json_decode($data, true);
    }

    /**
     * Заполнить мета данные.
     *
     * @param AbstractList $entity
     * @param Record $record
     * @param int $i
     */
    protected function fillAndSaveMetaData(AbstractList $entity, Record $record, $i)
    {
        $entityName = $entity->getEntity();
        $pageKey = $record->meta ? $entity->keyThreads : $entity->keyAlternativePagination;
        if (!isset($this->metaData[$entityName])) {
            $this->metaData[$entityName] = [
                $pageKey => null,
                'perPage' => $record->meta['perPage'],
            ];
            return;
        }
        if ($record->meta) {
            if ($this->metaData[$entityName][$pageKey] == $record->meta['currentPage']) {
                return;
            }
            $this->metaData[$entityName][$pageKey] = $record->meta['currentPage'];
        } else {
            $id = $record->data['id'];
            if ($i % $this->iterationCount != 0) {
                return;
            }
            $this->metaData[$entityName][$pageKey] = $id;
        }
        $this->saveMetaData();
    }

    /**
     * Записать в мета данные об успешном сохранение сущности.
     *
     * @param AbstractList $entity
     */
    protected function finishParseEntity(AbstractList $entity)
    {
        $entityName = $entity->getEntity();
        if (!isset($this->metaData[$entityName])) {
            $this->metaData[$entityName] = [];
        }
        $this->metaData[$entityName]['finish'] = true;
    }

    /**
     * Сохранить мета данные.
     */
    protected function saveMetaData()
    {
        $data = json_encode($this->metaData);
        file_put_contents($this->metaFilename, $data);
    }
}
