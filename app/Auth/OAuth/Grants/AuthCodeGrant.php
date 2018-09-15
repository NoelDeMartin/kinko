<?php

namespace Kinko\Auth\OAuth\Grants;

use League\OAuth2\Server\RequestEvent;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Grant\AuthCodeGrant as BaseGrant;

// The current implementation of league/oauth2-server does not allow using clients
// without secret for the authorization code grant, so until that is supported this
// functionallity needs to be overriden.

// see: https://github.com/thephpleague/oauth2-server/issues/889#issuecomment-414765620
class AuthCodeGrant extends BaseGrant
{
    protected function validateClient(ServerRequestInterface $request)
    {
        list($basicAuthUser, $basicAuthPassword) = $this->getBasicAuthCredentials($request);

        $clientId = $this->getRequestParameter('client_id', $request, $basicAuthUser);
        if (is_null($clientId)) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        // If the client is confidential require the client secret
        $clientSecret = $this->getRequestParameter('client_secret', $request, $basicAuthPassword);

        $client = $this->clientRepository->getClientEntity(
            $clientId,
            $this->getIdentifier(),
            $clientSecret,
            false
        );

        if ($client instanceof ClientEntityInterface === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::CLIENT_AUTHENTICATION_FAILED, $request));
            throw OAuthServerException::invalidClient();
        }

        $redirectUri = $this->getRequestParameter('redirect_uri', $request, null);

        if ($redirectUri !== null) {
            $this->validateRedirectUri($redirectUri, $client, $request);
        }

        return $client;
    }
}
