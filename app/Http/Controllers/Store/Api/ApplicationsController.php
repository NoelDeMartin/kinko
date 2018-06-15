<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Support\Facades\GraphQL;
use Kinko\Http\Controllers\Controller;
use Kinko\Http\Requests\StoreApplicationRequest;
use Kinko\Http\Requests\CreateApplicationRequest;

class ApplicationsController extends Controller
{
    public function create(CreateApplicationRequest $request)
    {
        return view('store.registration', [
            'name' => $request->input('name', ''),
            'url'  => $request->input('url'),
        ]);
    }

    public function store(StoreApplicationRequest $request)
    {
        // TODO at the moment, anyone can register a client and this could be exploited.
        // In order to fix this, clients should be created as not validated and manual validation should
        // be requested from users upon their first use.

        $schema = GraphQL::parseSchema($request->input('schema'));

        //
    }
}
