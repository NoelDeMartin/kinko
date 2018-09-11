<?php

namespace Kinko\Http\Requests\Concerns;

use Kinko\Exceptions\OAuthError;
use Illuminate\Contracts\Validation\Validator;

trait ThrowsOAuthErrors
{
    protected function failedValidation(Validator $validator)
    {
        throw new OAuthError('invalid_client_metadata', $validator->errors()->first());
    }
}
