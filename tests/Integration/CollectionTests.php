<?php

namespace Tests\Integration;

use Tests\TestCase;
use Kinko\Models\Collection;

class CollectionTests extends TestCase
{
    public function test_index()
    {
        $collections = factory(Collection::class, $this->faker->numberBetween(10, 20))->create();

        $response = $this->login()->get('/api/collections');

        $response->assertSuccessful();

        $collectionsJson = $response->json();
        $this->assertCount($collections->count(), $collectionsJson);
        foreach ($collections as $key => $collection) {
            $this->assertEquals($collection->resource()->resolve(), $collectionsJson[$key]);
        }
    }
}
