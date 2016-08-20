<?php

namespace SimaLand\API\Rest;

use SimaLand\API\Object;

class Response extends Object
{
    /**
     * @var array
     */
    public $items = [];

    /**
     * @var int
     */
    public $totalCount = 0;

    /**
     * @var int
     */
    public $pageCount = 0;

    /**
     * @var int
     */
    public $currentPage = 1;

    /**
     * @var int
     */
    public $perPage = 50;

    /**
     * @var string
     */
    public $rawBody;

    public function __construct($body, $contentType = '')
    {
        $this->rawBody = $body;
        if ($contentType == 'application/json') {
            $this->parseBody();
        }
    }

    private function parseBody()
    {
        $body = \GuzzleHttp\json_decode($this->rawBody, true);
        if (isset($body['items'])) {
            $this->items = $body['items'];
        }
        if (!isset($body['_meta'])) {
            return;
        }
        $meta = $body['_meta'];
        if (isset($meta['totalCount'])) {
            $this->totalCount = (int) $meta['totalCount'];
        }
        if (isset($meta['pageCount'])) {
            $this->pageCount = (int) $meta['pageCount'];
        }
        if (isset($meta['currentPage'])) {
            $this->currentPage = (int) $meta['currentPage'];
        }
        if (isset($meta['perPage'])) {
            $this->perPage = (int) $meta['perPage'];
        }
    }
}
