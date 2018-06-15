<?php

namespace Kinko\Http\Requests\Concerns;

trait AuthorizesRequests
{
    public function authorize()
    {
        return true;
    }
}
