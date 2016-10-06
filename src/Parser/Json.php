<?php

namespace SimaLand\API\Parser;

use SimaLand\API\Object;
use SimaLand\API\Record;

/**
 * Сохранение данных в json файл.
 */
class Json extends Object implements StorageInterface
{
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
     * Открыть файл на запись
     */
    public function open()
    {
        if (is_null($this->fileHandler)) {
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
    }

    /**
     * @@inheritdoc
     */
    public function save(Record $record)
    {
        $this->open();
        fputs($this->fileHandler, json_encode($record->data, JSON_UNESCAPED_UNICODE) . PHP_EOL);
    }

    /**
     * Уничтожить объект.
     */
    public function __destruct()
    {
        $this->close();
    }
}
