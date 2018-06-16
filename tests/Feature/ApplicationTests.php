<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kinko\Models\User;
use Kinko\Models\Application;
use Kinko\Models\Passport\Client;
use Tests\Concerns\FakesGuzzleRequests;

class ApplicationTests extends TestCase
{
    use FakesGuzzleRequests;

    public function test_validate()
    {
        $url = $this->faker->url;
        $domain = $this->faker->domainName;
        $callbackUrl = '/' . $this->faker->word;
        $description = $this->faker->sentence();
        $schemaUrl = '/autonomous-data-schema.graphql';

        $this->fakeGuzzleRequests();
        $this->appendGuzzleResponse(200, [], [
            'domain'       => $domain,
            'callback_url' => $callbackUrl,
            'description'  => $description,
            'schema_url'   => $schemaUrl,
        ]);
        $this->appendGuzzleResponse(200, [], file_get_contents(stubs_path('schema.graphql')));

        $response = $this->login()->get('/api/applications/validate?' . http_build_query([
            'url' => $url,
        ]));

        // TODO assert that guzzle was called

        $response->assertSuccessful();
        $response->assertJsonStructure(['domain', 'callback_url', 'description', 'schema_url', 'schema']);

        $details = $response->json();
        $this->assertEquals($domain, $details['domain']);
        $this->assertEquals('https://' . $domain . $callbackUrl, $details['callback_url']);
        $this->assertEquals($description, $details['description']);
        $this->assertEquals('https://' . $domain . $schemaUrl, $details['schema_url']);
        $this->assertEquals(load_stub('schema.json'), $details['schema']);
    }

    public function test_register()
    {
        $user = factory(User::class)->create();
        $name = $this->faker->sentence;
        $domain = $this->faker->domainName;
        $callbackUrl = 'http://' . $domain . '/' . $this->faker->word;
        $description = $this->faker->sentence();
        $schema = load_stub('schema.json');

        $response = $this->login($user)->post('/store/register', [
            'name' => $name,
            'domain' => $domain,
            'callback_url' => $callbackUrl,
            'description' => $description,
            'schema' => json_encode($schema),
        ]);

        $response->assertSuccessful();

        // TODO assert response redirection & domain called with client id and secret

        $this->assertEquals(1, Application::count());
        $this->assertEquals(1, Client::count());

        $application = Application::first();
        $client = Client::first();

        $this->assertEquals($name, $application->name);
        $this->assertEquals($domain, $application->domain);
        $this->assertEquals($callbackUrl, $application->callback_url);
        $this->assertEquals($description, $application->description);
        $this->assertEquals($schema, $application->schema);
        $this->assertEquals($client->id, $application->client_id);

        $this->assertEquals($name, $client->name);
        $this->assertEquals($callbackUrl, $client->redirect);
        $this->assertEquals($user->id, $client->user_id);
        $this->assertFalse($client->personal_access_client);
        $this->assertFalse($client->password_client);
        $this->assertFalse($client->revoked);
    }

    // TODO test failing scenarios
}
