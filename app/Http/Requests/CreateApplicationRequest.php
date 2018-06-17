<?php

namespace Kinko\Http\Requests;

use Kinko\Http\Requests\Rules\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Kinko\Http\Requests\Concerns\AuthorizesRequests;
use Kinko\Http\Requests\Rules\UrlDomain;

class CreateApplicationRequest extends FormRequest
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
            'state' => 'required|string',
            'name' => 'string',
            'description' => 'required|string',
            'domain' => ['required', new Domain],
            'callback_url' => ['required', new UrlDomain($this, 'domain')],
            'redirect_url' => ['required', new UrlDomain($this, 'domain')],
            'schema_url' => 'required|url',
        ];
    }
}
