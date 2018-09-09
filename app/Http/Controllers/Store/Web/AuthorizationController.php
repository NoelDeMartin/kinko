<?php

namespace Kinko\Http\Controllers\Store\Web;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Kinko\Models\Passport\Client;
use Laravel\Passport\Bridge\User;
use Kinko\Http\Controllers\Controller;
use Zend\Diactoros\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Contracts\Routing\ResponseFactory;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\Http\Controllers\RetrievesAuthRequestFromSession;

class AuthorizationController extends Controller
{
    use HandlesOAuthErrors, RetrievesAuthRequestFromSession;

    protected $server;

    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    public function create(ServerRequestInterface $psrRequest, Request $request) {
        return $this->withErrorHandling(function () use ($psrRequest, $request) {
            $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

            $scopes = $this->parseScopes($authRequest);

            $client = Client::find($authRequest->getClient()->getIdentifier());
            $user = $request->user();
            $token = $client
                ->tokens()
                ->whereUserId($user->getKey())
                ->whereRevoked(false)
                ->where('expires_at', '>', now())
                ->latest('expires_at')
                ->first();

            if (!is_null($token) && $token->scopes === collect($scopes)->pluck('id')->all()) {
                return $this->approveRequest($authRequest, $user);
            }

            $request->session()->put('authRequest', $authRequest);

            return view('store.authorize', [
                'client' => $client,
                'user' => $user,
                'scopes' => $scopes,
                'state' => $request->state,
            ]);
        });
    }

    public function approve(Request $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            $authRequest = $this->getAuthRequestFromSession($request);

            return $this->convertResponse(
                $this->server->completeAuthorizationRequest($authRequest, new Psr7Response)
            );
        });
    }

    public function deny(Request $request)
    {
        $authRequest = $this->getAuthRequestFromSession($request);

        $clientUris = Arr::wrap($authRequest->getClient()->getRedirectUri());

        if (!in_array($uri = $authRequest->getRedirectUri(), $clientUris)) {
            $uri = Arr::first($clientUris);
        }

        $separator = $authRequest->getGrantTypeId() === 'implicit' ? '#' : (strstr($uri, '?') ? '&' : '?');

        return redirect()->to(
            $uri . $separator . 'error=access_denied&state=' . $request->input('state')
        );
    }

    protected function parseScopes($authRequest)
    {
        return Passport::scopesFor(
            collect($authRequest->getScopes())->map(function ($scope) {
                return $scope->getIdentifier();
            })->unique()->all()
        );
    }

    protected function approveRequest($authRequest, $user)
    {
        $authRequest->setUser(new User($user->getKey()));

        $authRequest->setAuthorizationApproved(true);

        return $this->convertResponse(
            $this->server->completeAuthorizationRequest($authRequest, new Psr7Response)
        );
    }
}
