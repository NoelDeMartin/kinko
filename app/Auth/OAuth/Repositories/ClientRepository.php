<?php

namespace Kinko\Auth\OAuth\Repositories;

use Kinko\Models\Client;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity($clientIdentifier, $grantType = null, $clientSecret = null, $mustValidateSecret = true)
    {
        $client = Client::find($clientIdentifier);

        if (is_null($client)) {
            return;
        }

        if (!is_null($grantType) && $grantType !== 'authorization_code') {
            return;
        }

        if ($mustValidateSecret && !password_verify($clientSecret, $client->secret)) {
            return;
        }

        return $client;
    }
}
