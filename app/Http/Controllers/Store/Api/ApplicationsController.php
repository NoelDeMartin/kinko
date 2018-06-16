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
        //
    }
}
