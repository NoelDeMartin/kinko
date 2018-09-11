<?php

namespace Kinko\Http\Requests;

use Kinko\Http\Requests\Rules\Domain;
use Kinko\Http\Requests\Rules\UrlDomain;
use Illuminate\Foundation\Http\FormRequest;
use Kinko\Http\Requests\Rules\ApplicationSchemaJson;
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
            'state'        => 'required|string',
            'name'         => 'required|string|unique:applications',
            'description'  => 'required|string',
            'domain'       => ['required', new Domain, 'unique:applications'],
            'callback_url' => ['required', 'secure_url'],
            'redirect_url' => ['required', 'secure_url'],
            'schema'       => ['required', new ApplicationSchemaJson],
        ];
    }
}
