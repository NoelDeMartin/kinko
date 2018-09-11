<?php

namespace Tests\Integration\OAuth;

use Kinko\Models\Client;

class RFC7591Tests extends OAuthTestCase
{
    public function test_dynamic_client_registration()
    {
        $domain = $this->faker->domainName;
        $homeUrl = 'http://' . $domain;
        $redirectUrl = 'http://' . $domain . '/' . $this->faker->word;

        $response = $this->post('store/clients', [
            'client_name' => $this->faker->sentence,
            'client_uri' => $homeUrl,
            'client_description' => $this->faker->sentence,
            'redirect_uris' => [$redirectUrl],
            'token_endpoint_auth_method' => 'none',
            'grant_types' => [
                'authorization_code',
            ],
            'response_types' => [
                'code',
            ],
            'schema' => file_get_contents(stubs_path('schema.graphql')),
        ]);

        $response->assertSuccessful();

        $this->assertEquals(1, Client::count());

        // TODO add more assertions
    }

    public function test_dynamic_client_registration_error()
    {
        $domain = $this->faker->domainName;
        $homeUrl = 'http://' . $domain;
        $redirectUrl = 'http://' . $domain . '/' . $this->faker->word;

        $response = $this->post('store/clients', [
            'client_name' => $this->faker->sentence,
            'client_uri' => $homeUrl,
            'client_description' => $this->faker->sentence,
            'redirect_uris' => [$redirectUrl],
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
