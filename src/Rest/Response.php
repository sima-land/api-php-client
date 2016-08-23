<?php

namespace SimaLand\API\Rest;

use SimaLand\API\Object;

/**
 * Response from sima-land.
 */
class Response extends Object
{
    /**
     * @var array
     */
    public $body;

    /**
     * @var int
     */
    public $statusCode;

    /**
     * @var array
     */
    public $headers;

    /**
     * @var string
     */
    public $rawBody;

    /**
     * @var string
     */
    public $reasonPhrase;

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(\Psr\Http\Message\ResponseInterface $response)
    {
        $this->statusCode = $response->getStatusCode();
        $this->headers = $response->getHeaders();
        $this->rawBody = $response->getBody()->getContents();
        $this->reasonPhrase = $response->getReasonPhrase();
        $this->body = json_decode($response->getBody(), true);
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return ($this->statusCode >= 200 && $this->statusCode < 300);
    }
}
