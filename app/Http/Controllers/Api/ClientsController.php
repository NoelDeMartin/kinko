<?php

namespace Kinko\Http\Controllers\Api;

use Kinko\Models\Client;
use Kinko\Http\Controllers\Controller;

class ClientsController extends Controller
{
    public function index()
    {
        return resource(Client::all());
    }
}
