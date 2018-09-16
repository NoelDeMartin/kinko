<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Models\User;
use Kinko\Models\Application;
use Kinko\Models\AccessToken;
use Kinko\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

class GraphQLController
{
    public function __invoke(ResourceServer $server, ServerRequestInterface $request)
    {
        // TODO handle unauthentication properly
        $request = $server->validateAuthenticatedRequest($request);

        $accessToken = AccessToken::with('client', 'user')
            ->where('id', $request->getAttribute('oauth_access_token_id'))
            ->first();

        Auth::setUser($accessToken->user);

        return GraphQL::query($accessToken->client, $request);
    }
}
