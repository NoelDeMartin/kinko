<?php

namespace Kinko\Http\Controllers\Store\Web;

use Kinko\Models\Client;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\User;
use Kinko\Http\Controllers\Controller;
use Zend\Diactoros\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Contracts\Routing\ResponseFactory;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Kinko\Http\Controllers\Store\Web\Concerns\RetrievesAuthRequestFromSession;

class AuthorizationController extends Controller
{
    use HandlesOAuthErrors, RetrievesAuthRequestFromSession;

    protected $server;

    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    public function create(ServerRequestInterface $psrRequest, Request $request) {
        // TODO refactor error handling and display (see https://tools.ietf.org/html/rfc6749#section-4.1.2.1)
        $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

        $client = Client::find($authRequest->getClient()->getIdentifier());
        $user = $request->user();
        $token = $client
            ->tokens()
            ->where('user_id', $user->id)
            ->where('revoked', false)
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
            'state' => $request->state,
        ]);
    }

    public function approve(Request $request)
    {
        // TODO refactor error handling and display (see https://tools.ietf.org/html/rfc6749#section-4.1.2.1)
        $authRequest = $this->getAuthRequestFromSession($request);

        $response = $this->server->completeAuthorizationRequest($authRequest, new Psr7Response);

        $client = $authRequest->getClient();

        if (!$client->validated) {
            $client->update([
                'validated' => true,
                'user_id' => $request->user()->id,
            ]);
        }

        return $this->convertResponse($response);
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

    protected function approveRequest($authRequest, $user)
    {
        $authRequest->setUser($user);

        $authRequest->setAuthorizationApproved(true);

        return $this->convertResponse(
            $this->server->completeAuthorizationRequest($authRequest, new Psr7Response)
        );
    }
}
