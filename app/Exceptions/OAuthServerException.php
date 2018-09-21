<?php

namespace Kinko\Exceptions;

use Illuminate\Http\Response;
use Zend\Diactoros\Response as Psr7Response;
use League\OAuth2\Server\Exception\OAuthServerException as BaseException;

class OAuthServerException extends BaseException
{
    public static function invalidClientMetadata($description = null)
    {
        $errorMessage = 'Client metadata is invalid';

        $exception = new static($errorMessage, 12, 'invalid_client_metadata', 400);

        if (!is_null($description)) {
            $payload = $exception->getPayload();
            $payload['error_description'] = $description;
            $exception->setPayload($payload);
        }

        return $exception;
    }

    public function render()
    {
        $psrResponse = $this->generateHttpResponse(new Psr7Response);

        return new Response(
            $psrResponse->getBody(),
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );
    }
}
