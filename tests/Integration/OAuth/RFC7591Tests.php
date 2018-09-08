<?php

namespace Tests\Integration\OAuth;

use Kinko\Models\Application;
use Kinko\Models\Passport\Client;

class RFC7591Tests extends OAuthTestCase
{
    public function test_dynamic_client_registration()
    {
        $domain = $this->faker->domainName;
        $homeUrl = 'http://' . $domain;
        $callbackUrl = 'http://' . $domain . '/' . $this->faker->word;
        $redirectUrl = 'http://' . $domain . '/' . $this->faker->word;

        $response = $this->post('store/clients', [
            'client_name' => $this->faker->sentence,
            'client_uri' => $homeUrl,
            'domain' => $domain,
            'redirect_uris' => [$redirectUrl],
            'description' => $this->faker->sentence,
            'token_endpoint_auth_method' => 'none',
            'grant_types' => [
                'authorization_code',
            ],
            'response_types' => [
                'code',
            ],
            'schema' => file_get_contents(stubs_path('schema.graphql')),
        ]);

        $this->assertEquals(1, Client::count());
        $this->assertEquals(1, Application::count());

        // TODO add more assertions
    }

    public function test_dynamic_client_registration_error()
    {
        $domain = $this->faker->domainName;
        $homeUrl = 'http://' . $domain;
        $callbackUrl = 'http://' . $domain . '/' . $this->faker->word;
        $redirectUrl = 'http://' . $domain . '/' . $this->faker->word;

        $response = $this->post('store/clients', [
            'client_name' => $this->faker->sentence,
            'client_uri' => $homeUrl,
            'domain' => $domain,
            'redirect_uris' => [$redirectUrl],
            'description' => $this->faker->sentence,
            'token_endpoint_auth_method' => 'client_secret_post',
            'grant_types' => [
                'authorization_code',
            ],
            'response_types' => [
                'code',
            ],
            'schema' => file_get_contents(stubs_path('schema.graphql')),
        ]);

        $response->assertOAuthError(
            'invalid_client_metadata',
            'Non-public clients are not supported with dynamic client registration.'
        );
    }

    // TODO test other possible errors
    // TODO review specification
}
