<?php

namespace Kinko\Http\Requests\Concerns;

use Kinko\Exceptions\OAuthServerException;
use Illuminate\Contracts\Validation\Validator;

trait ThrowsOAuthErrors
{
    protected function failedValidation(Validator $validator)
    {
        throw OAuthServerException::invalidClientMetadata(
            $validator->errors()->first()
        );
    }
}
