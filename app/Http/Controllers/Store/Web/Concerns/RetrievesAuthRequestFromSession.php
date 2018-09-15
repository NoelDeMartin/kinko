<?php

namespace Kinko\Http\Controllers\Store\Web\Concerns;

use Exception;
use Illuminate\Http\Request;

trait RetrievesAuthRequestFromSession
{
    protected function getAuthRequestFromSession(Request $request)
    {
        return tap($request->session()->get('authRequest'), function ($authRequest) use ($request) {
            if (!$authRequest) {
                throw new Exception('Authorization request was not present in the session.');
            }

            $authRequest->setUser($request->user());

            $authRequest->setAuthorizationApproved(true);
        });
    }
}
