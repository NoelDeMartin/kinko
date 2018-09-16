<?php

namespace Kinko\Http\Controllers\Store\Api\Concerns;

use Exception;
use Throwable;
use Illuminate\Http\Response;
use Zend\Diactoros\Response as Psr7Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

trait HandlesOAuthErrors
{
    protected function withErrorHandling($callback)
    {
        try {
            return $callback();
        } catch (OAuthServerException $e) {
            report($e);

            $psrResponse = $e->generateHttpResponse(new Psr7Response);

            return new Response(
                $psrResponse->getBody(),
                $psrResponse->getStatusCode(),
                $psrResponse->getHeaders()
            );
        } catch (Exception $e) {
            report($e);

            return new Response(config('app.debug') ? $e->getMessage() : 'Error.', 500);
        } catch (Throwable $e) {
            report(new FatalThrowableError($e));

            return new Response(config('app.debug') ? $e->getMessage() : 'Error.', 500);
        }
    }
}
