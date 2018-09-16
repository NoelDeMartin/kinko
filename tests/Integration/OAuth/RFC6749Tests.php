<?php

namespace Tests\Integration\OAuth;

use Kinko\Models\User;
use Kinko\Models\Client;
use Defuse\Crypto\Crypto;
use Kinko\Models\AuthCode;
use Kinko\Models\AccessToken;
use Kinko\Models\Application;
use Kinko\Models\RefreshToken;

/**
 * Test RFC 6749 implementation.
 *
 * https://tools.ietf.org/html/rfc6749
 */
class RFC6749Tests extends OAuthTestCase
{
    public function test_code_authorization_approval_validates_clients()
    {
        $user = factory(User::class)->create();
        $client = factory(Client::class)->create([
            'user_id' => null,
            'validated' => false,
        ]);
        $state = str_random();

        $params = [
            'response_type' => 'code',
            'client_id' => $client->id,
            'redirect_uri' => $client->redirect_uris[0],
            'state' => $state,
        ];

        // Open form to prepare session
        $response = $this->login($user)->get('/store/authorize?' . http_build_query($params));

        $response->assertSuccessful();
        $response->assertSessionHas('authRequest');

        // TODO assert that form shows data about non-validated app (schema, etc.)

        // Submit form
        $response = $this->post('/store/authorize', $params);

        $response->assertRedirect();
        $redirectLocation = $response->headers->get('Location');
        $redirectUrl = substr($redirectLocation, 0, strpos($redirectLocation, '?'));
        $redirectParams = [];
        parse_str(
            substr($redirectLocation, strpos($redirectLocation, '?') + 1),
            $redirectParams
        );

        // TODO validate scopes

        $this->assertTrue($client->fresh()->validated);
        $this->assertEquals($user->id, $client->fresh()->user_id);

        $this->assertEquals($client->redirect_uris[0], $redirectUrl);
        $this->assertArrayHasKey('state', $redirectParams);
        $this->assertArrayHasKey('code', $redirectParams);
        $this->assertEquals($state, $redirectParams['state']);
        $this->assertEquals(1, AuthCode::count());

        $authCode = AuthCode::first();
        $this->assertEquals($client->id, $authCode->client_id);
        $this->assertEquals($user->id, $authCode->user_id);
        $this->assertFalse($authCode->revoked);
        $this->assertTrue($authCode->expires_at->isFuture());

        $codeData = json_decode(Crypto::decryptWithPassword(
            $redirectParams['code'],
            app('encrypter')->getKey()
        ));
        $this->assertEquals($codeData->client_id, $client->id);
        $this->assertEquals($codeData->user_id, $user->id);
        $this->assertEquals($codeData->auth_code_id, $authCode->id);
        $this->assertEquals($codeData->redirect_uri, $client->redirect_uris[0]);
    }

    // TODO test that auth code only works with the same user that validated it

    // TODO test authorization code with existing valid access token

    public function test_code_authorization_rejection()
    {
        $client = factory(Client::class)->create([
            'user_id' => null,
            'validated' => false,
        ]);
        $state = str_random();

        $params = [
            'response_type' => 'code',
            'client_id' => $client->id,
            'redirect_uri' => $client->redirect_uris[0],
            'state' => $state,
        ];

        // Open form to prepare session
        $response = $this->login()->get('/store/authorize?' . http_build_query($params));

        $response->assertSuccessful();
        $response->assertSessionHas('authRequest');

        // Post form to obtain code
        $response = $this->delete('/store/authorize', $params);

        $response->assertRedirect();
        $redirectLocation = $response->headers->get('Location');
        $redirectUrl = substr($redirectLocation, 0, strpos($redirectLocation, '?'));
        $redirectParams = [];
        parse_str(
            substr($redirectLocation, strpos($redirectLocation, '?') + 1),
            $redirectParams
        );

        $this->assertEquals($client->redirect_uris[0], $redirectUrl);
        $this->assertArrayHasKey('state', $redirectParams);
        $this->assertArrayHasKey('error', $redirectParams);
        $this->assertEquals($state, $redirectParams['state']);
        $this->assertEquals('access_denied', $redirectParams['error']);
    }

    public function test_authorization_code_grant_issue_token()
    {
        $user = factory(User::class)->create();
        $client = factory(Client::class)->create([
            'user_id' => $user->id,
            'validated' => true,
        ]);
        $authCode = AuthCode::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'scopes' => [], // TODO real scopes
            'revoked' => false,
            'expires_at' => now()->addHour(),
        ]);

        $code = Crypto::encryptWithPassword(
            json_encode([
                'client_id' => $client->id,
                'redirect_uri' => $client->redirect_uris[0],
                'auth_code_id' => $authCode->id,
                'scopes' => $authCode->scopes,
                'user_id' => $user->id,
                'expire_time' => $authCode->expires_at->format('U'),
                'code_challenge' => null,
                'code_challenge_method' => null,
            ]),
            app('encrypter')->getKey()
        );

        $response = $this->post('/store/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $client->id,
            'redirect_uri' => $client->redirect_uris[0],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token',
            // TODO scope
        ]);

        $this->assertEquals(1, AccessToken::count());
        $this->assertEquals(1, RefreshToken::count());

        $accessToken = AccessToken::first();
        $refreshToken = RefreshToken::first();

        $this->assertEquals('Bearer', $response->json('token_type'));
        $this->assertEquals($user->id, $accessToken->user_id);
        $this->assertEquals($client->id, $accessToken->client_id);
        $this->assertTrue($accessToken->expires_at->isFuture());
        $this->assertFalse($accessToken->revoked);
        $this->assertEquals($accessToken->id, $refreshToken->access_token_id);
        $this->assertFalse($refreshToken->revoked);
        $this->assertTrue($refreshToken->expires_at->isFuture());
    }

    // TODO issue token checks that client is validated
}
