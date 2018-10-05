<?php

namespace Kinko\Http\Controllers\Store\Web;

use Exception;
use Kinko\Models\Client;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Kinko\Models\Collection;
use Kinko\Http\Controllers\Controller;
use Zend\Diactoros\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Contracts\Routing\ResponseFactory;
use Kinko\Http\Controllers\Store\Api\Concerns\ConvertsPsrResponses;

class AuthorizationController extends Controller
{
    use ConvertsPsrResponses;

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

        // TODO show collisions if any (with existing collection & data)
        // TODO resolve collision conflicts (if schemas are different)

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
            $types = $client->getSchemaTypes();

            foreach ($types as $name => $typeDefinition) {
                $collection = strtolower(str_plural($name));

                if (Collection::where('name', $collection)->count() === 0) {
                    Collection::create([
                        'name' => $collection,
                        'type' => $typeDefinition,
                    ]);
                } else {
                    // TODO resolve collision conflicts if any
                }

            }

            $client->update([
                'validated' => true,
                'user_id' => $request->user()->id,
            ]);
        }

        return $this->convertPsrResponse($response);
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

        return $this->convertPsrResponse(
            $this->server->completeAuthorizationRequest($authRequest, new Psr7Response)
        );
    }

    protected function getAuthRequestFromSession(Request $request)
    {
        return tap($request->session()->get('authRequest'), function ($authRequest) use ($request) {
            if (!$authRequest) {
                throw new Exception('Authorization request was not present in the session.');
            }

            $authRequest->setUser($request->user());

            $authRequest->setAuthorizationApproved(true);
        });
    }
}
