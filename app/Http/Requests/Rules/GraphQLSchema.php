<?php

namespace Kinko\Http\Requests\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Kinko\Support\Facades\GraphQL;

class GraphQLSchema implements Rule
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
            $schema = GraphQL::parseSchema($value);

            $types = array_keys($schema->getTypeMap());

            return !in_array('Query', $types)
                && !in_array('Mutation', $types)
                && !in_array('Subscription', $types);
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
        return 'Invalid GraphQL Schema.';
    }
}
