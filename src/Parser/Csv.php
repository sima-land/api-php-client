<?php

namespace SimaLand\API\Parser;

use SimaLand\API\Object;

/**
 * Сохранение данных в *.csv файл.
 */
class Csv extends Object implements StorageInterface
{
    /**
     * Символ разделения полей.
     *
     * @var string
     */
    public $delimiter = ';';

    /**
     * Символ разделителя текста.
     *
     * @var string
     */
    public $enclosure = '"';

    /**
     * Пулный путь до файла.
     *
     * @var
     */
    public $filename;

    /**
     * Указатель на файл.
     *
     * @var
     */
    private $fileHandler;

    /**
     * Флаг записи название полей в файл.
     *
     * @var bool
     */
    private $isSaveHeader = false;

    /**
     * Открыть файл на запись
     */
    public function open()
    {
        if (is_null($this->fileHandler)) {
            $this->fileHandler = fopen($this->filename, "w");
            $this->isSaveHeader = false;
        }
    }

    /**
     * Закрыть файл.
     */
    public function close()
    {
        if ($this->fileHandler) {
            fclose($this->fileHandler);
            $this->fileHandler = null;
        }
        $this->isSaveHeader = false;
    }

    /**
     * @@inheritdoc
     */
    public function save($item)
    {
        $this->open();
        if (!$this->isSaveHeader) {
            $keys = array_keys($item);
            fputcsv($this->fileHandler, $keys, $this->delimiter, $this->enclosure);
            $this->isSaveHeader = true;
        }
        fputcsv($this->fileHandler, $item, $this->delimiter, $this->enclosure);
    }

    /**
     * Уничтожить объект.
     */
    public function __destruct()
    {
        $this->close();
    }
}
