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
 * $itemStorage = new Csv(['filename' => 'path/to/item.csv']);
 * $categoryList = new \SimaLand\API\Entities\CategoryList($client);
 * $categoryStorage = new Csv(['filename' => 'path/to/category.csv']);
 * $parser = new Parser(['metaFilename' => 'path/to/file']);
 * $parser->addEntity($itemList, $itemStorage);
 * $parser->addEntity($categoryList, $categoryStorage);
 * $parser->run();
 *
 * ```
 */
class Parser extends Object
{
    public $countRecordsSave = 1000;

    /**
     * @var array
     */
    private $list = [];

    /**
     * Путь до файла с мета данными.
     *
     * @var string
     */
    private $metaFilename;

    /**
     * Мета данные.
     *
     * @var array
     */
    private $metaData = [];

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
            /** @var StorageInterface  $storage */
            $storage = $el['storage'];
            foreach ($entity as $key => $record) {
                if ($continue) {
                    $this->fillMetaData($entity, $record, $key);
                    $this->saveMetaData();
                }
                $storage->save($record);
            }
            if ($continue) {
                $this->finishParseEntity($entity);
                $this->saveMetaData();
            }
        }
    }

    /**
     * Загрузить мета данные.
     */
    private function loadMetaData()
    {
        if (!file_exists($this->metaFilename)) {
            return;
        }
        $data = file_get_contents($this->metaFilename);
        $this->metaData = json_decode($data, true);
    }

    /**
     * Заполнить мета данные.
     *
     * @param AbstractList $entity
     * @param Record $record
     * @param int $i
     */
    private function fillMetaData(AbstractList $entity, Record $record, $i)
    {
        $entityName = $entity->getEntity();
        if ($record->meta) {
            if (!isset($this->metaData[$entityName])) {
                $this->metaData[$entityName] = [
                    $entity->keyThreads => $record->meta['currentPage'],
                    'perPage' => $record->meta['perPage'],
                ];
                return;
            }
            if ($this->metaData[$entityName][$entity->keyThreads] == $record->meta['currentPage']) {
                return;
            }
            $this->metaData[$entityName][$entity->keyThreads] = $record->meta['currentPage'];
        } else {
            $id = $record->data['id'];
            if (!isset($this->metaData[$entityName])) {
                $this->metaData[$entityName] = [
                    $entity->keyAlternativePagination => $id,
                    'perPage' => isset($entity->getParams['perPage']) ? $entity->getParams['perPage'] : null,
                ];
                return;
            }
            if ($i % $this->countRecordsSave != 0) {
                return;
            }
            $this->metaData[$entityName][$entity->keyAlternativePagination] = $id;
        }
    }

    /**
     * Записать в мета данные об успешном сохранение сущности.
     *
     * @param AbstractList $entity
     */
    private function finishParseEntity(AbstractList $entity)
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
    private function saveMetaData()
    {
        $data = json_encode($this->metaData);
        file_put_contents($this->metaFilename, $data);
    }
}
