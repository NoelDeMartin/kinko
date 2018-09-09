<?php

namespace Tests\Integration\OAuth;

use Kinko\Models\User;
use Kinko\Models\Application;
use Kinko\Models\Passport\Client;
use Kinko\Models\Passport\AuthCode;
use Kinko\Models\Passport\AccessToken;
use Kinko\Models\Passport\RefreshToken;

class RFC6749Tests extends OAuthTestCase
{
    public function test_authorization_code_grant()
    {
        $user = factory(User::class)->create();
        $client = factory(Client::class)->create();
        $state = str_random();

        factory(Application::class)->create(['client_id' => $client->id]);

        $params = [
            'response_type' => 'code',
            'client_id' => $client->id,
            'redirect_uri' => $client->redirect,
            'state' => $state,
            // TODO scope
        ];

        // Open form to prepare session
        $response = $this->login($user)->get('/store/authorize?' . http_build_query($params));

        $response->assertSuccessful();
        $response->assertSessionHas('authRequest');

        // TODO assert that form shows data about non-validated app (schema, etc.)

        // Post form to obtain code
        $response = $this->post('/store/authorize', $params);

        $response->assertRedirect();
        $redirectLocation = $response->headers->get('Location');
        $redirectUrl = substr($redirectLocation, 0, strpos($redirectLocation, '?'));
        $redirectParams = [];
        parse_str(
            substr($redirectLocation, strpos($redirectLocation, '?') + 1),
            $redirectParams
        );

        $this->assertEquals($client->redirect, $redirectUrl);
        $this->assertArrayHasKey('state', $redirectParams);
        $this->assertArrayHasKey('code', $redirectParams);
        $this->assertEquals($state, $redirectParams['state']);
        $this->assertEquals(1, AuthCode::count());

        $authCode = AuthCode::first();
        $this->assertEquals($client->id, $authCode->client_id);
        $this->assertEquals($user->id, $authCode->user_id);
        $this->assertFalse($authCode->revoked);
        $this->assertTrue($authCode->expires_at->isFuture());

        // Exchange code for access_token
        $response = $this->post('/store/token', [
            'grant_type' => 'authorization_code',
            'code' => $redirectParams['code'],
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'redirect_uri' => $client->redirect,
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

    public function test_authorization_code_grant_rejection()
    {
        $user = factory(User::class)->create();
        $client = factory(Client::class)->create();
        $state = str_random();

        factory(Application::class)->create(['client_id' => $client->id]);

        $params = [
            'response_type' => 'code',
            'client_id' => $client->id,
            'redirect_uri' => $client->redirect,
            'state' => $state,
            // TODO scope
        ];

        // Open form to prepare session
        $response = $this->login($user)->get('/store/authorize?' . http_build_query($params));

        $response->assertSuccessful();

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

        $this->assertEquals($client->redirect, $redirectUrl);
        $this->assertArrayHasKey('state', $redirectParams);
        $this->assertArrayHasKey('error', $redirectParams);
        $this->assertEquals($state, $redirectParams['state']);
        $this->assertEquals('access_denied', $redirectParams['error']);
    }
}
