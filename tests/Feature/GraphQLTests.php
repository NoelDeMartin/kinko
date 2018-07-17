<?php

namespace Tests\Feature;

use Tests\TestCase;
use Kinko\Models\User;
use Kinko\Models\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Kinko\Support\Facades\MongoDB;

class GraphQLTests extends TestCase
{
    public function test_introspection()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $response = $this->login()->graphql('{__schema{types{name}}}');

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

        $response = $this->login()->graphql('{allTasks{id}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['allTasks']]);

        $this->assertCount($tasksCount, $response->json('data.allTasks'));
    }

    public function test_mutation_create()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $user = factory(User::class)->create();
        $name = $this->faker->sentence;
        $description = $this->faker->sentence;
        $now = now();
        Carbon::setTestNow($now);

        $response = $this->login($user)->graphql(
            "mutation {
                createTask(
                    name: \"$name\",
                    description: \"$description\",
                ) {
                    id,
                    name,
                    description,
                    author_id,
                    created_at,
                    updated_at
                }
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['createTask' => ['id', 'name', 'description', 'author_id', 'created_at', 'updated_at']]]);

        $this->assertEquals(1, DB::collection('store-tasks')->count());
        $this->assertEquals($name, $response->json('data.createTask.name'));
        $this->assertEquals($description, $response->json('data.createTask.description'));
        $this->assertEquals($user->id, $response->json('data.createTask.author_id'));
        $this->assertEquals($now->getTimestamp(), $response->json('data.createTask.created_at'));
        $this->assertEquals($now->getTImestamp(), $response->json('data.createTask.updated_at'));
    }

    public function test_mutation_update()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $user = factory(User::class)->create();
        $name = $this->faker->sentence;
        $now = now();
        $id = DB::collection('store-tasks')->insertGetId([
            'name' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'created_at' => MongoDB::date($now),
            'updated_at' => MongoDB::date($now),
        ]);

        $later = $now->copy()->addDay();
        Carbon::setTestNow($later);

        $response = $this->login($user)->graphql(
            "mutation {
                updateTask(
                    id: \"$id\",
                    name: \"$name\",
                    description: null,
                ) {
                    id,
                    name,
                    description,
                    created_at,
                    updated_at
                }
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['updateTask' => ['id', 'name', 'description', 'created_at', 'updated_at']]]);

        $this->assertEquals($id, $response->json('data.updateTask.id'));
        $this->assertEquals($name, $response->json('data.updateTask.name'));
        $this->assertNull($response->json('data.updateTask.description'));
        $this->assertEquals($now->getTimestamp(), $response->json('data.updateTask.created_at'));
        $this->assertEquals($later->getTimestamp(), $response->json('data.updateTask.updated_at'));

        $document = DB::collection('store-tasks')->first();
        $this->assertArrayHasKey('name', $document);
        $this->assertArrayNotHasKey('description', $document);
        $this->assertArrayHasKey('created_at', $document);
        $this->assertArrayHasKey('updated_at', $document);
    }

    public function test_mutation_primary_key_protected()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $id = str_random();
        $name = $this->faker->sentence;

        $response = $this->login()->graphql("mutation {
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
