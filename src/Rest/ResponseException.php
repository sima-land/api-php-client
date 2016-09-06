<?php

namespace SimaLand\API\Rest;

use Psr\Http\Message\ResponseInterface;
use SimaLand\API\Exception;

class ResponseException extends Exception
{
    public function __construct(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), true);
        $message = isset($body['message']) ? $body['message'] : $response->getReasonPhrase();
        parent::__construct($message, $response->getStatusCode());
    }
}
