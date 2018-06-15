<?php

namespace Kinko\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Kinko\Http\Requests\Rules\GraphQLSchema;
use Kinko\Http\Requests\Concerns\AuthorizesRequests;

class StoreApplicationRequest extends FormRequest
{
    use AuthorizesRequests;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'   => 'required|string|unique:clients',
            'schema' => ['required', new GraphQLSchema],
        ];
    }
}
