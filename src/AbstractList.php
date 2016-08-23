<?php

namespace SimaLand\API;

use SimaLand\API\Rest\Client;
use SimaLand\API\Rest\Request;

abstract class AbstractList
{
    /**
     * Count threads.
     *
     * @var int
     */
    public $countThreads = 5;

    /**
     * Key query param of thread.
     *
     * @var string
     */
    public $keyThreads = 'page';

    /**
     * SimaLand Client for queries.
     *
     * @var \SimaLand\API\Rest\Client
     */
    private $client;

    /**
     * @var Request[]
     */
    private $requests = [];

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    abstract function getEntity();

    /**
     * @param Request $request
     * @param null $item
     */
    public function assignPage(Request &$request, $item = null)
    {
        $currentPage = 0;
        if ($item and isset($item['_meta']['currentPage'])) {
            $currentPage = (int) $item['_meta']['currentPage'];
        }
        if (!is_array($request->getParams)) {
            $request->getParams = (array) $request->getParams;
        }
        if ($currentPage > 0) {
            $request->getParams[$this->keyThreads] = $currentPage + $this->countThreads;
        }
    }

    /**
     * @param Request $request
     * @param int $number
     */
    public function assignThreadsNumber(Request &$request, $number = 0)
    {
        if ($number == 0) {
            return;
        }
        if (!is_array($request->getParams)) {
            $request->getParams = (array) $request->getParams;
        }
        $request->getParams[$this->keyThreads] = $number;
    }

    /**
     * Key which contains the entity of records.
     *
     * @return string
     */
    public function getCollectionKey()
    {
        return 'items';
    }

    /**
     * @return Rest\Response[]
     * @throws \Exception
     */
    public function get()
    {
        return $this->client->batchQuery($this->getRequests());
    }

    /**
     * Load all records entity.
     *
     * @return Iterator
     */
    public function batch()
    {
        return new Iterator($this);
    }

    /**
     * @param Request[] $requests
     * @throws \Exception
     */
    public function setRequests(array $requests)
    {
        $this->requests = [];
        foreach ($requests as $request) {
            if (!$request instanceof Request) {
                throw new \Exception('Request must be implement "\SimaLand\API\Rest\Request"');
            }
            $this->requests[] = $request;
        }
    }

    /**
     * @return array|Rest\Request[]
     */
    public function getRequests()
    {
        if (empty($this->requests)) {
            $requests = [];
            if (!is_null($this->keyThreads) && $this->countThreads > 1) {
                for ($i = 0; $i < $this->countThreads; $i++) {
                    $requests[$i] = new Request([
                        'entity' => $this->getEntity(),
                    ]);
                    $this->assignThreadsNumber($requests[$i], $i);
                }
            } else {
                $requests[] = new Request(['entity' => $this->getEntity()]);
            }
            $this->requests = $requests;
        }
        return $this->requests;
    }
}
