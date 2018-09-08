<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Models\Application;
use Kinko\Exceptions\OAuthError;
use Kinko\Models\Passport\Client;
use Kinko\Http\Controllers\Controller;
use Kinko\Http\Requests\StoreClientRequest;

class ClientsController extends Controller
{
    public function store(StoreClientRequest $request)
    {
        if ($request->input('token_endpoint_auth_method') !== 'none') {
            throw new OAuthError(
                'invalid_client_metadata',
                'Non-public clients are not supported with dynamic client registration.'
            );
        }

        if (
            $request->has('grant_types') &&
            $request->input('grant_types') !== ['authorization_code']
        ) {
            throw new OAuthError(
                'invalid_client_metadata',
                '"authorization_code" is the only supported grant type at the momment.'
            );
        }

        if (
            $request->has('response_types') &&
            $request->input('response_types') !== ['code']
        ) {
            throw new OAuthError(
                'invalid_client_metadata',
                '"code" is the only supported response type at the momment.'
            );
        }

        $client = Client::create([
            'user_id' => null,
            'name' => $request->input('client_name'),
            'secret' => null,
            'redirect' => $request->input('redirect_uris'),
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
        ]);

        $applicationData = [
            'name' => $request->input('client_name'),
            'description' => $request->input('description'),
            'domain' => $request->input('domain'),
            'schema' => $request->input('schema'),
            'client_id' => $client->id,
            'validated' => false,
        ];

        if ($request->has('logo_uri')) {
            $applicationData['logo_url'] = $request->input('logo_uri');
        }

        if ($request->has('client_uri')) {
            $applicationData['homepage_url'] = $request->input('client_uri');
        }

        $application = Application::create($applicationData);

        $responseData = [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'description' => $application->description,
            'domain' => $application->domain,
            'redirect_uris' => [$client->redirect],
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code'],
            'response_type' => ['code'],
            'schema' => '', // TODO json
        ];

        if ($request->has('logo_uri')) {
            $responseData['logo_uri'] = $application->logo_url;
        }

        if ($request->has('client_uri')) {
            $responseData['client_uri'] = $application->homepage_url;
        }

        return response()
            ->json($responseData)
            ->setStatusCode(201);
    }
}
