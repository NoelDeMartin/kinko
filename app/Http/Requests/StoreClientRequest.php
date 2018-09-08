<?php

namespace Kinko\Http\Requests;

use Illuminate\Validation\Rule;
use Kinko\Http\Requests\Rules\Domain;
use Kinko\Http\Requests\Rules\UrlDomain;
use Illuminate\Foundation\Http\FormRequest;
use Kinko\Http\Requests\Rules\ApplicationSchema;
use Kinko\Http\Requests\Concerns\AuthorizesRequests;

class StoreClientRequest extends FormRequest
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
            'client_name' => 'required|string',
            'client_uri' => [new UrlDomain($this, 'domain')],
            'logo_uri' => 'url',
            'description' => 'required|string',
            'domain' => ['required', new Domain],
            'redirect_uris' => 'required|array',
            'required_uris.*' => [new UrlDomain($this, 'domain')],
            'token_endpoint_auth_method' => [
                'required',
                Rule::in(['none', 'client_secret_post', 'client_secret_basic']),
            ],
            'grant_types' => 'array',
            'grant_types.*' => [
                Rule::in([
                    'authorization_code',
                    'implicit',
                    'password',
                    'client_credentials',
                    'refresh_token',
                    'urn:...',
                    'urn:...',
                ]),
            ],
            'response_types' => 'array',
            'response_types.*' => 'in:code,token',
            'schema' => ['required', new ApplicationSchema],
        ];
    }
}
