<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Http\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response as Psr7Response;
use League\OAuth2\Server\AuthorizationServer;
use Kinko\Http\Controllers\Store\Api\Concerns\HandlesOAuthErrors;
use Kinko\Http\Controllers\Store\Api\Concerns\ConvertsPsrResponses;

class AccessTokenController extends Controller
{
    use ConvertsPsrResponses, HandlesOAuthErrors;

    protected $server;

    public function __construct(AuthorizationServer $server) {
        $this->server = $server;
    }

    public function issueToken(ServerRequestInterface $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            return $this->convertPsrResponse(
                $this->server->respondToAccessTokenRequest($request, new Psr7Response)
            );
        });
    }
}
