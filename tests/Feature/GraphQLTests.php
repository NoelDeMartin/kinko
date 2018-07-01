<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kinko\Models\Application;
use Illuminate\Support\Facades\DB;

class GraphQLTests extends TestCase
{
    public function test_introspection()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $response = $this->graphql('{__schema{types{name}}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['__schema' => ['types' => []]]]);

        $names = collect($response->json('data.__schema.types'))->map->name;
        $this->assertContains('User', $names);
    }

    public function test_query()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $usersCount = random_int(5, 10);
        $this->createUsers($usersCount);

        $response = $this->graphql('{allUsers{id}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['allUsers']]);

        $this->assertCount($usersCount, $response->json('data.allUsers'));
    }

    public function test_mutation()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $name = $this->faker->name;
        $email = $this->faker->email;

        $response = $this->graphql('mutation {createUser(name: "' . $name . '", email: "' . $email . '") {id, name, email}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['createUser' => ['id', 'name', 'email']]]);

        $this->assertEquals(1, DB::collection('store-users')->count());
        $this->assertEquals($name, $response->json('data.createUser.name'));
        $this->assertEquals($email, $response->json('data.createUser.email'));
    }

    private function graphql($query)
    {
        return $this->post('/store', compact('query'));
    }

    private function createUsers($count = 1, $attributes = [])
    {
        for ($i = 0; $i < $count; $i++) {
            DB::collection('store-users')->insert(array_merge([
                'name' => $this->faker->name,
                'email' => $this->faker->email,
            ], $attributes));
        }
    }
}
