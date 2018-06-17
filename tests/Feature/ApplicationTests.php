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

    public function test_parse_schema()
    {
        $url = $this->faker->url;

        $this->fakeGuzzleRequests();
        $this->appendGuzzleResponse(200, [], file_get_contents(stubs_path('schema.graphql')));

        $response = $this->login()->get('/api/applications/parse_schema?' . http_build_query([
            'url' => $url,
        ]));

        $response->assertSuccessful();

        $this->assertGuzzleCalled($url);

        $this->assertEquals(load_stub('schema.json'), $response->json());
    }

    public function test_register()
    {
        $state = str_random();
        $user = factory(User::class)->create();
        $name = $this->faker->sentence;
        $domain = $this->faker->domainName;
        $callbackUrl = 'http://' . $domain . '/' . $this->faker->word;
        $redirectUrl = 'http://' . $domain . '/' . $this->faker->word;
        $description = $this->faker->sentence();
        $schema = load_stub('schema.json');

        $this->fakeGuzzleRequests();
        $this->appendGuzzleResponse();

        $response = $this->login($user)->post('/store/register', [
            'state' => $state,
            'name' => $name,
            'domain' => $domain,
            'callback_url' => $callbackUrl,
            'redirect_url' => $redirectUrl,
            'description' => $description,
            'schema' => json_encode($schema),
        ]);

        $response->assertRedirect($redirectUrl);

        $this->assertEquals(1, Application::count());
        $this->assertEquals(1, Client::count());

        $application = Application::first();
        $client = Client::first();

        $this->assertGuzzleCalled($callbackUrl . '?' . http_build_query([
            'state' => $state,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]));

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
