<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kinko\Models\User;
use Kinko\Models\Application;
use Kinko\Models\Passport\Client;
use Kinko\Models\Passport\AuthCode;
use Kinko\Models\Passport\AccessToken;
use Kinko\Models\Passport\RefreshToken;

class AuthorizationTests extends TestCase
{
    public function test_code_authorization()
    {
        $user = factory(User::class)->create();
        $client = factory(Client::class)->create();
        $application = factory(Application::class)->create(['client_id' => $client->id]);
        $state = str_random();

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
        $this->assertFalse($accessToken->revoked);
        $this->assertEquals($accessToken->id, $refreshToken->access_token_id);
        $this->assertFalse($refreshToken->revoked);
    }
}
