<?php

namespace Kinko\Http\Controllers\Store\Api\Concerns;

use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

trait ConvertsPsrResponses
{
    public function convertPsrResponse(ResponseInterface $psrResponse)
    {
        return new Response(
            $psrResponse->getBody(),
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );
    }
}
