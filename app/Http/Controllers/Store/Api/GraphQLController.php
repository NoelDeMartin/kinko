<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Models\Application;
use Kinko\Support\Facades\GraphQL;
use Psr\Http\Message\ServerRequestInterface;

class GraphQLController
{
    public function __invoke(ServerRequestInterface $request)
    {
        // TODO use authentication to retrieve application
        $application = Application::first();

        return GraphQL::query($application, $request);
    }
}