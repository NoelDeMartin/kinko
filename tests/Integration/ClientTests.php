<?php

namespace Tests\Integration;

use Tests\TestCase;
use Kinko\Models\User;
use Kinko\Models\Client;
use Tests\Concerns\FakesGuzzleRequests;

class ClientTests extends TestCase
{
    use FakesGuzzleRequests;

    public function test_index()
    {
        $user = factory(User::class)->create();
        $clients = factory(Client::class, random_int(10, 20))->create();

        $response = $this->get('/api/clients', [
            'Authorization' => 'Bearer ' . $user->api_token,
        ]);

        $response->assertSuccessful();

        $clientsJson = $response->json();
        $this->assertCount($clients->count(), $clientsJson);
        foreach ($clients as $key => $client) {
            $this->assertEquals($client->resource()->resolve(), $clientsJson[$key]);
        }
    }
}
