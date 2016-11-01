<?php

namespace SimaLand\API;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Базовый класс.
 */
class Object
{
    /**
     * Наименование лога.
     */
    const LOGGER_NAME = 'SimaAPI';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Получить логгер.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            $this->logger = new Logger(self::LOGGER_NAME);
            $this->logger->pushHandler(new StreamHandler('php://output'));
        }
        return $this->logger;
    }

    /**
     * Установить логгер.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $setter = 'set' . ucfirst($key);
        if (method_exists($this, $setter)) {
            call_user_func([$this, $setter], $value);
            return;
        }
        $this->{$key} = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $getter = 'get' . ucfirst($key);
        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        }
        return $this->{$key};
    }
}
