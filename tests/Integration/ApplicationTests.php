<?php

namespace Tests\Integration;

use Tests\TestCase;
use Kinko\Models\User;
use Kinko\Models\Client;
use Kinko\Models\Application;
use Tests\Concerns\FakesGuzzleRequests;

class ApplicationTests extends TestCase
{
    use FakesGuzzleRequests;

    public function test_index()
    {
        $user = factory(User::class)->create();
        $applications = collect();
        $clients = factory(Client::class, $this->faker->numberBetween(10, 20))
            ->create()
            ->each(function ($client) use (&$applications) {
                $applications->push(factory(Application::class)->create(['client_id' => $client->id]));
            });

        $response = $this->get('/api/applications', [
            'Authorization' => 'Bearer ' . $user->api_token,
        ]);

        $response->assertSuccessful();

        $applicationsJson = $response->json();
        $this->assertCount($applications->count(), $applicationsJson);
        foreach ($applications as $key => $application) {
            $this->assertEquals($application->resource()->resolve(), $applicationsJson[$key]);
        }
    }
}
