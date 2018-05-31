<?php

namespace Kinko\Http\Controllers\Api;

use Kinko\Models\Collection;
use Kinko\Http\Controllers\Controller;

class CollectionsController extends Controller
{
    public function index()
    {
        return resource(Collection::all());
    }
}
