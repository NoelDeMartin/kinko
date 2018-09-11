<?php

namespace Kinko\Http\Requests;

use Illuminate\Validation\Rule;
use Kinko\Http\Requests\Rules\Domain;
use Kinko\Http\Requests\Rules\UrlDomain;
use Illuminate\Foundation\Http\FormRequest;
use Kinko\Http\Requests\Rules\ApplicationSchema;
use Kinko\Http\Requests\Concerns\ThrowsOAuthErrors;
use Kinko\Http\Requests\Concerns\AuthorizesRequests;

class StoreClientRequest extends FormRequest
{
    use ThrowsOAuthErrors, AuthorizesRequests;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'client_name' => 'required|string',
            'client_uri' => ['url', new UrlDomain($this, 'redirect_uris')],
            'client_description' => 'required|string',
            // TODO scope
            'logo_uri' => 'url', // TODO validate valid url image
            'redirect_uris' => 'required|array',
            'required_uris.*' => ['url', new UrlDomain($this, 'redirect_uris')],
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
