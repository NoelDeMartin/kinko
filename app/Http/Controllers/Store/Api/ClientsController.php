<?php

namespace Kinko\Http\Controllers\Store\Api;

use Kinko\Models\Client;
use Kinko\Exceptions\OAuthError;
use Kinko\Support\Facades\GraphQL;
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

        $clientData = [
            'name' => $request->input('client_name'),
            'description' => $request->input('client_description'),
            'redirect_uris' => $request->input('redirect_uris'),
            'schema' => GraphQL::parseGraphQLSchema($request->input('schema')),
        ];

        if ($request->has('logo_uri')) {
            $clientData['logo_url'] = $request->input('logo_uri');
        }

        if ($request->has('client_uri')) {
            $clientData['homepage_url'] = $request->input('client_uri');
        }

        $client = Client::create($clientData);

        $responseData = [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'client_description' => $client->description,
            // TODO scope
            'redirect_uris' => $client->redirect_uris,
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code'],
            'response_type' => ['code'],
            'schema' => $client->schema,
        ];

        if ($request->has('logo_uri')) {
            $responseData['logo_uri'] = $client->logo_url;
        }

        if ($request->has('client_uri')) {
            $responseData['client_uri'] = $client->homepage_url;
        }

        return response()
            ->json($responseData)
            ->setStatusCode(201);
    }
}
