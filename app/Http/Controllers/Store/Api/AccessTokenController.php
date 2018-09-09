<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Http\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response as Psr7Response;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;

class AccessTokenController extends Controller
{
    use HandlesOAuthErrors;

    protected $server;

    public function __construct(AuthorizationServer $server) {
        $this->server = $server;
    }

    public function issueToken(ServerRequestInterface $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            return $this->convertResponse(
                $this->server->respondToAccessTokenRequest($request, new Psr7Response)
            );
        });
    }
}
