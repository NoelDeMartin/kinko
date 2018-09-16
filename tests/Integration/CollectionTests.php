<?php

namespace Tests\Integration;

use Tests\TestCase;
use Kinko\Models\User;
use Kinko\Models\Collection;

class CollectionTests extends TestCase
{
    public function test_index()
    {
        $user = factory(User::class)->create();
        $collections = factory(Collection::class, $this->faker->numberBetween(10, 20))->create();

        $response = $this->get('/api/collections', [
            'Authorization' => 'Bearer ' . $user->api_token,
        ]);

        $response->assertSuccessful();

        $collectionsJson = $response->json();
        $this->assertCount($collections->count(), $collectionsJson);
        foreach ($collections as $key => $collection) {
            $this->assertEquals($collection->resource()->resolve(), $collectionsJson[$key]);
        }
    }
}
