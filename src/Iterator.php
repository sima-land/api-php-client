<?php

namespace SimaLand\API;

use SimaLand\API\Rest\Response;

/**
 * Iterator for load all records entity.
 */
class Iterator implements \Iterator
{
    /**
     * @var AbstractList
     */
    private $owner;

    /**
     * @var array
     */
    private $values = [];

    /**
     * @var int
     */
    private $key;

    /**
     * @var mixed
     */
    private $current;

    /**
     * @param AbstractList $owner
     */
    public function __construct(AbstractList $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @param Response $response
     * @return bool
     * @throws \Exception
     */
    private function isResponse(Response $response)
    {
        if (!$response->isOk() && $response->statusCode != 404) {
            if ($response->body && isset($response->body['message'])) {
                $message = $response->body['message'];
            } else {
                $message = $response->reasonPhrase;
            }
            throw new \Exception($message, $response->statusCode);
        } elseif ($response->statusCode == 404 || !$response->body) {
            return false;
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    private function getData()
    {
        $responses = $this->owner->get();
        $collectionKey = $this->owner->getCollectionKey();
        $requests = $this->owner->getRequests();
        $item = null;
        foreach ($responses as $key => $response) {
            if (!$this->isResponse($response)) {
                unset($requests[$key]);
                continue;
            }
            foreach ($response->body[$collectionKey] as $item) {
                $this->values[] = $item;
            }
            if (!is_null($item)) {
                $this->owner->assignPage($requests[$key], $item);
            }
        }
        $this->owner->setRequests($requests);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        if (empty($this->values)) {
            $this->getData();
        }
        $this->current = array_shift($this->values);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->key++;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return !empty($this->current);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->values = [];
        $this->current = null;
        $this->key = 0;
        $this->next();
    }
}