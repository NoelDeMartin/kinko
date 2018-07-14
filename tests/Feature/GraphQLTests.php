<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kinko\Models\Application;
use Illuminate\Support\Carbon;
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
        $this->assertContains('Task', $names);
    }

    public function test_query()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $tasksCount = random_int(5, 10);
        $this->createTasks($tasksCount);

        $response = $this->graphql('{allTasks{id}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['allTasks']]);

        $this->assertCount($tasksCount, $response->json('data.allTasks'));
    }

    public function test_mutation()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $name = $this->faker->sentence;
        $now = now();
        Carbon::setTestNow($now);

        $response = $this->graphql(
            "mutation {
                createTask(
                    name: \"$name\",
                ) {
                    id,
                    name,
                    created_at,
                    updated_at
                }
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['createTask' => ['id', 'name', 'created_at', 'updated_at']]]);

        $this->assertEquals(1, DB::collection('store-tasks')->count());
        $this->assertEquals($name, $response->json('data.createTask.name'));
        $this->assertEquals($now->getTimestamp(), $response->json('data.createTask.created_at'));
        $this->assertEquals($now->getTImestamp(), $response->json('data.createTask.updated_at'));
    }

    public function test_mutation_primary_key_protected()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $id = str_random();
        $name = $this->faker->sentence;

        $response = $this->graphql("mutation {
            createTask(
                id: \"$id\",
                name: \"$name\",
            ) {
                id,
                name,
            }
        }");

        $response->assertGraphQLError('Unknown argument "id" on field "createTask" of type "Mutation".');
    }

    private function graphql($query)
    {
        return $this->post('/store', compact('query'));
    }

    private function createTasks($count = 1, $attributes = [])
    {
        for ($i = 0; $i < $count; $i++) {
            DB::collection('store-tasks')->insert(array_merge([
                'name' => $this->faker->sentence,
            ], $attributes));
        }
    }
}
