<?php

namespace SimaLand\API\Parser;

use SimaLand\API\Object;

class Csv extends Object implements StorageInterface
{
    public $path;

    public $delimiter = ';';

    public $enclosure = '"';

    public $escapeChar = '\'';

    private $filename;

    private $fileHandler;

    private $isSaveHeader = false;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        if (!file_exists($this->path)) {
            throw new \Exception("Path '{$this->path}' not find");
        }
    }

    public function open()
    {
        $this->fileHandler = fopen($this->filename, "w");
    }

    public function close()
    {
        fclose($this->fileHandler);
        $this->isSaveHeader = false;
    }

    /**
     * @@inheritdoc
     */
    public function save($item)
    {
        if (!$this->isSaveHeader) {
            $keys = array_keys($item);
            fputcsv($this->fileHandler, $keys, $this->delimiter, $this->enclosure, $this->escapeChar);
            $this->isSaveHeader = true;
        }
        fputcsv($this->fileHandler, $item, $this->delimiter, $this->enclosure, $this->escapeChar);
    }

    /**
     * @inheritdoc
     */
    public function setEntity($entity)
    {
        $path = $this->path;
        if (substr($path, -1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
        $this->filename = $path . $entity . ".csv";
        $this->isSaveHeader = false;
        $this->open();
    }
}
