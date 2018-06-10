<?php

namespace Kinko\Http\Controllers\Web\AutonomousData;

use Kinko\Http\Controllers\Controller;

class ClientRegistrationController extends Controller
{
    public function create()
    {
        return view('autonomous_data.registration');
    }

    public function store()
    {
        // TODO
    }
}
