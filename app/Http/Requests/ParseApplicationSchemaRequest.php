<?php

namespace Kinko\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Kinko\Http\Requests\Concerns\AuthorizesRequests;

class ParseApplicationSchemaRequest extends FormRequest
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
            'url' => 'required|url',
        ];
    }
}
