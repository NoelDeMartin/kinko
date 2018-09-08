<?php

namespace Kinko\Http\Requests\Rules;

use Exception;
use Kinko\Support\Facades\GraphQL;
use Illuminate\Contracts\Validation\Rule;

class ApplicationSchema implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            GraphQL::parseGraphQLSchema($value, true);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid application schema.';
    }
}
