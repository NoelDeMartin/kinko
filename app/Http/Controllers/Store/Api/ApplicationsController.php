<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Models\Application;
use Kinko\Models\Passport\Client;
use Kinko\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Auth;
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
        $client = Client::create([
            'user_id' => Auth::id(),
            'name' => $request->input('name'),
            'secret' => str_random(40),
            'redirect' => $request->input('callback_url'),
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ]);

        $application = Application::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'domain' => $request->input('domain'),
            'callback_url' => $request->input('callback_url'),
            'schema' => GraphQL::parseJson($request->input('schema'))->toArray(),
            'client_id' => $client->id,
        ]);

        // TODO communicate client_id & client_secret to application domain

        return 'redirect to ' . $application->callback_url;
    }
}
