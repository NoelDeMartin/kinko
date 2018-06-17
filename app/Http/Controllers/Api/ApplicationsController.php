<?php

namespace Kinko\Http\Controllers\Api;

use Kinko\Models\Application;
use Kinko\Http\Controllers\Controller;

class ApplicationsController extends Controller
{
    public function index()
    {
        return resource(Application::all());
    }
}
