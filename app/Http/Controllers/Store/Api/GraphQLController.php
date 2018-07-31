<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Models\User;
use Kinko\Models\Application;
use Kinko\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\ServerRequestInterface;

class GraphQLController
{
    public function __invoke(ServerRequestInterface $request)
    {
        $application = Auth::user()->token()->client->application;

        return GraphQL::query($application, $request);
    }
}
