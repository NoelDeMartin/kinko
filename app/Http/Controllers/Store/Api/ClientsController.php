<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Exceptions\OAuthError;
use Kinko\Http\Controllers\Controller;
use Kinko\Http\Requests\StoreClientRequest;

class ClientsController extends Controller
{
    public function store(StoreClientRequest $request)
    {
        if ($request->input('token_endpoint_auth_method') !== 'none') {
            throw new OAuthError(
                'invalid_client_metadata',
                'Non-public clients are not supported with dynamic client registration'
            );
        }

        // TODO return non supported grant_types
        // TODO return non supported response_types

        // TODO
    }
}
