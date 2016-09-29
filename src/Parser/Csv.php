<?php

namespace SimaLand\API\Parser;

use SimaLand\API\Object;
use SimaLand\API\Record;

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
            if (file_exists($this->filename)) {
                $this->isSaveHeader = true;
            } else {
                $this->isSaveHeader = false;
            }
            $this->fileHandler = fopen($this->filename, "a+");
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
    public function save(Record $record)
    {
        $this->open();
        $item = $record->data;
        foreach ($item as &$value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
        }
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
