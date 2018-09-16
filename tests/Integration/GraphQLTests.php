<?php

namespace Tests\Integration;

use Tests\TestCase;
use Kinko\Models\User;
use Kinko\Models\Client;
use Illuminate\Http\Response;
use Kinko\Models\AccessToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Kinko\Support\Facades\MongoDB;
use League\OAuth2\Server\CryptKey;
use Kinko\Auth\OAuth\Entities\AccessToken as AccessTokenEntity;

class GraphQLTests extends TestCase
{
    public function test_require_login()
    {
        $response = $this->postJson('/store', ['query' => '{ping}']);

        $response->assertStatus(500);
    }

    public function test_introspection()
    {
        $response = $this->graphql('{__schema{types{name}}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['__schema' => ['types' => []]]]);

        $names = collect($response->json('data.__schema.types'))->map->name;
        $this->assertContains('Task', $names);
    }

    public function test_ping()
    {
        $this->withoutExceptionHandling();

        $response = $this->graphql('{ping}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['ping']]);

        $this->assertEquals('pong', $response->json('data.ping'));
    }

    public function test_query()
    {
        $tasksCount = random_int(5, 10);
        $this->createTasks($tasksCount);

        $response = $this->graphql('{tasks: getTasks{id}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['tasks']]);

        $this->assertCount($tasksCount, $response->json('data.tasks'));
    }

    public function test_query_find_one()
    {
        $task = $this->createTasks(random_int(5, 10))->random();

        $response = $this->graphql(
            "{
                task: getTask(id: \"{$task->id}\") {
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

        $this->assertTaskEquals($task, $response->json('data.task'));
    }

    public function test_query_simple_filter()
    {
        $tasks = $this->createTasks(random_int(5, 10))->random(random_int(2, 4));

        $name = $this->faker->unique()->sentence;
        foreach ($tasks as $task) {
            $task->name = $name;
            DB::collection('store.tasks')
                ->where('_id', MongoDB::key($task->id))
                ->update(compact('name'));
        }

        $response = $this->graphql(
            "{
                tasks: getTasks(filter: {
                    field: \"name\",
                    operation: EQUALS,
                    value: \"{$name}\"
                }) {
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

        $responseTasks = $response->json('data.tasks');
        $this->assertCount($tasks->count(), $responseTasks);

        foreach ($responseTasks as $i => $task) {
            $this->assertTaskEquals($tasks[$i], $task);
        }
    }

    public function test_query_complex_filter()
    {
        $this->createTasks(1, ['name' => 'a', 'description' => 'c']);
        $this->createTasks(1, ['name' => 'b', 'description' => 'a']);
        $tasks = collect([
            $this->createTasks(1, ['name' => 'a', 'description' => 'a']),
            $this->createTasks(1, ['name' => 'a', 'description' => 'b']),
        ]);

        $response = $this->graphql(
            "{
                tasks: getTasks(filter: {
                    AND: [
                        {
                            field: \"name\",
                            value: \"a\"
                        },
                        {
                            OR: [
                                {
                                    field: \"description\",
                                    value: \"a\"
                                },
                                {
                                    field: \"description\",
                                    value: \"b\"
                                }
                            ]
                        }
                    ]
                }) {
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

        $responseTasks = $response->json('data.tasks');
        $this->assertCount($tasks->count(), $responseTasks);

        foreach ($responseTasks as $i => $task) {
            $this->assertTaskEquals($tasks[$i], $task);
        }
    }

    public function test_query_simple_order_by()
    {
        $tasks = collect([
            $this->createTasks(1, ['name' => 'a']),
            $this->createTasks(1, ['name' => 'b']),
            $this->createTasks(1, ['name' => 'c']),
        ])->reverse()->values();

        $response = $this->graphql(
            "{
                tasks: getTasks(orderBy: {
                    field: \"name\",
                    direction: DESCENDING
                }) {
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

        $responseTasks = $response->json('data.tasks');
        $this->assertCount($tasks->count(), $responseTasks);

        foreach ($responseTasks as $i => $task) {
            $this->assertTaskEquals($tasks[$i], $task);
        }
    }

    public function test_query_complex_order_by()
    {
        $tasks = collect([
            $this->createTasks(1, ['name' => 'a', 'description' => 'b']),
            $this->createTasks(1, ['name' => 'a', 'description' => 'a']),
            $this->createTasks(1, ['name' => 'b', 'description' => 'c']),
            $this->createTasks(1, ['name' => 'c', 'description' => 'c']),
        ])->reverse()->values();

        $response = $this->graphql(
            "{
                tasks: getTasks(orderBy: {
                    AND: [
                        {
                            field: \"name\",
                            direction: DESCENDING
                        },
                        {
                            field: \"description\"
                        }
                    ]
                }) {
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

        $responseTasks = $response->json('data.tasks');
        $this->assertCount($tasks->count(), $responseTasks);

        foreach ($responseTasks as $i => $task) {
            $this->assertTaskEquals($tasks[$i], $task);
        }
    }

    public function test_query_limit()
    {
        $tasks = $this->createTasks(random_int(5, 10));
        $limit = intval($tasks->count() / 2);

        $response = $this->graphql(
            "{
                tasks: getTasks(limit: {$limit}) {
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

        $responseTasks = $response->json('data.tasks');
        $this->assertCount($limit, $responseTasks);

        foreach ($responseTasks as $i => $task) {
            $this->assertTaskEquals($tasks[$i], $task);
        }
    }

    public function test_query_offset()
    {
        $tasks = $this->createTasks(random_int(5, 10));
        $offset = intval($tasks->count() / 2);

        $response = $this->graphql(
            "{
                tasks: getTasks(offset: {$offset}) {
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

        $responseTasks = $response->json('data.tasks');
        $this->assertCount($tasks->count() - $offset, $responseTasks);

        foreach ($responseTasks as $i => $task) {
            $this->assertTaskEquals($tasks[$i + $offset], $task);
        }
    }

    public function test_mutation_create()
    {
        $user = factory(User::class)->create();
        $name = $this->faker->sentence;
        $description = $this->faker->sentence;
        $now = now();
        Carbon::setTestNow($now);

        $response = $this->graphql(
            "mutation {
                task: createTask(
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
            }",
            $user
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['task' => ['id', 'name', 'description', 'author_id', 'created_at', 'updated_at']]]);

        $this->assertEquals(1, DB::collection('store.tasks')->count());
        $this->assertEquals($name, $response->json('data.task.name'));
        $this->assertEquals($description, $response->json('data.task.description'));
        $this->assertEquals($user->id, $response->json('data.task.author_id'));
        $this->assertEquals($now->getTimestamp(), $response->json('data.task.created_at'));
        $this->assertEquals($now->getTImestamp(), $response->json('data.task.updated_at'));
    }

    public function test_mutation_update_one()
    {
        $name = $this->faker->sentence;
        $now = now();
        $id = DB::collection('store.tasks')->insertGetId([
            'name' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'created_at' => MongoDB::date($now),
            'updated_at' => MongoDB::date($now),
        ]);

        $later = $now->copy()->addDay();
        Carbon::setTestNow($later);

        $response = $this->graphql(
            "mutation {
                task: updateTask(
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
        $response->assertJsonStructure(['data' => ['task' => ['id', 'name', 'description', 'created_at', 'updated_at']]]);

        $this->assertEquals($id, $response->json('data.task.id'));
        $this->assertEquals($name, $response->json('data.task.name'));
        $this->assertNull($response->json('data.task.description'));
        $this->assertEquals($now->getTimestamp(), $response->json('data.task.created_at'));
        $this->assertEquals($later->getTimestamp(), $response->json('data.task.updated_at'));

        $document = DB::collection('store.tasks')->first();
        $this->assertArrayHasKey('name', $document);
        $this->assertArrayNotHasKey('description', $document);
        $this->assertArrayHasKey('created_at', $document);
        $this->assertArrayHasKey('updated_at', $document);
    }

    public function test_mutation_update_many_and_return_count()
    {
        $originalName = $this->faker->sentence;
        $updatedName = $this->faker->sentence;

        $tasks = $this->createTasks(random_int(5, 10));
        $mutatedTasks = $this->createTasks(random_int(5, 10), [
            'name' => $originalName,
        ]);

        $response = $this->graphql(
            "mutation {
                count: updateTasks(
                    filter: {
                        field: \"name\",
                        value: \"$originalName\"
                    },
                    values: {
                        name: \"$updatedName\",
                        description: null,
                    }
                )
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['count']]);

        $this->assertEquals($mutatedTasks->count(), $response->json('data.count'));

        foreach ($tasks as $task) {
            $updatedTask = DB::collection('store.tasks')->where('_id', MongoDB::key($task->id))->first();
            $this->assertEquals($task->name, $updatedTask['name']);
            $this->assertEquals($task->description, $updatedTask['description']);
            $this->assertEquals($task->author_id, $updatedTask['author_id']);
            $this->assertEquals($task->id, $updatedTask['_id']);
            $this->assertEquals($task->created_at->getTimestamp(), $updatedTask['created_at']->toDateTime()->getTimestamp());
            $this->assertEquals($task->updated_at->getTimestamp(), $updatedTask['updated_at']->toDateTime()->getTimestamp());
        }

        foreach ($mutatedTasks as $task) {
            $updatedTask = DB::collection('store.tasks')->where('_id', MongoDB::key($task->id))->first();
            $this->assertEquals($updatedName, $updatedTask['name']);
            $this->assertArrayNotHasKey('description', $updatedTask);
            $this->assertEquals($task->author_id, $updatedTask['author_id']);
            $this->assertEquals($task->created_at->getTimestamp(), $updatedTask['created_at']->toDateTime()->getTimestamp());
            $this->assertEquals($task->updated_at->getTimestamp(), $updatedTask['updated_at']->toDateTime()->getTimestamp());
        }
    }

    public function test_mutation_update_many_and_return_objects()
    {
        $originalName = $this->faker->sentence;
        $updatedName = $this->faker->sentence;

        $tasks = $this->createTasks(random_int(5, 10));
        $mutatedTasks = $this->createTasks(random_int(5, 10), [
            'name' => $originalName,
        ]);

        $response = $this->graphql(
            "mutation {
                tasks: updateTasksAndReturnObjects(
                    filter: {
                        field: \"name\",
                        value: \"$originalName\"
                    },
                    values: {
                        name: \"$updatedName\",
                        description: null,
                    }
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
        $response->assertJsonStructure(['data' => ['tasks' => ['*' => ['id', 'name', 'description', 'created_at', 'updated_at']]]]);

        $this->assertCount($mutatedTasks->count(), $response->json('data.tasks'));

        foreach ($response->json('data.tasks') as $i => $task) {
            $this->assertEquals($updatedName, $task['name']);
            $this->assertNull($task['description']);
            $this->assertEquals($mutatedTasks[$i]->author_id, $task['author_id']);
            $this->assertEquals($mutatedTasks[$i]->created_at->getTimestamp(), $task['created_at']);
            $this->assertEquals($mutatedTasks[$i]->updated_at->getTimestamp(), $task['updated_at']);
        }
    }

    public function test_mutation_delete_one()
    {
        $name = $this->faker->sentence;
        $now = now();
        $id = DB::collection('store.tasks')->insertGetId([
            'name' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'created_at' => MongoDB::date($now),
            'updated_at' => MongoDB::date($now),
        ]);

        $response = $this->graphql(
            "mutation {
                result: deleteTask(id: \"$id\")
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['result']]);

        $this->assertEquals(0, DB::collection('store.tasks')->count());
        $this->assertTrue($response->json('data.result'));
    }

    public function test_mutation_delete_many_and_return_count()
    {
        $name = $this->faker->sentence;

        $tasks = $this->createTasks(random_int(5, 10));
        $removedTasks = $this->createTasks(random_int(5, 10), [
            'name' => $name,
        ]);

        $response = $this->graphql(
            "mutation {
                count: deleteTasks(
                    filter: {
                        field: \"name\",
                        value: \"$name\"
                    }
                )
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['count']]);

        $this->assertEquals($removedTasks->count(), $response->json('data.count'));

        foreach ($tasks as $task) {
            $this->assertNotNull(DB::collection('store.tasks')->where('_id', MongoDB::key($task->id))->first());
        }

        foreach ($removedTasks as $task) {
            $this->assertNull(DB::collection('store.tasks')->where('_id', MongoDB::key($task->id))->first());
        }
    }

    public function test_mutation_delete_many_and_return_ids()
    {
        $name = $this->faker->sentence;

        $tasks = $this->createTasks(random_int(5, 10));
        $removedTasks = $this->createTasks(random_int(5, 10), [
            'name' => $name,
        ]);

        $response = $this->graphql(
            "mutation {
                ids: deleteTasksAndReturnIds(
                    filter: {
                        field: \"name\",
                        value: \"$name\"
                    }
                )
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['ids']]);

        $this->assertCount($removedTasks->count(), $response->json('data.ids'));

        foreach ($response->json('data.ids') as $i => $id) {
            $this->assertEquals($removedTasks[$i]->id, $id);
        }
    }

    public function test_mutation_primary_key_protected()
    {
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

    private function graphql($query, $user = null)
    {
        $user = $user ?? factory(User::class)->create();

        $client = factory(Client::class)->create([
            'user_id' => $user->id,
            'validated' => true,
            'schema' => load_stub('schema.json'),
        ]);

        $accessToken = factory(AccessToken::class)->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
        ]);

        $OAuthAccessToken = new AccessTokenEntity($user->id);
        $OAuthAccessToken->setClient($client);
        $OAuthAccessToken->setExpiryDateTime(now()->addMonth());
        $OAuthAccessToken->setIdentifier($accessToken->id);

        $privateKey = new CryptKey('file://' . storage_path('oauth-private.key'), null, false);
        $token = (string) $OAuthAccessToken->convertToJWT($privateKey);

        return $this->postJson('/store', compact('query'), [
            'Authorization' => 'Bearer ' . $token,
        ]);
    }

    private function createTasks($count = 1, $attributes = [])
    {
        $tasks = collect();
        for ($i = 0; $i < $count; $i++) {
            $now = now();
            $task = array_merge([
                'name' => $this->faker->sentence,
                'description' => $this->faker->sentence,
                'author_id' => str_random(),
                'created_at' => MongoDB::date($now),
                'updated_at' => MongoDB::date($now),
            ], $attributes);
            $task['id'] = (string) DB::collection('store.tasks')->insertGetId($task);
            $task['created_at'] = $task['created_at']->toDateTime();
            $task['updated_at'] = $task['updated_at']->toDateTime();
            $tasks->push((object) $task);
        }

        return $count > 1 ? $tasks : $tasks[0];
    }

    private function assertTaskEquals($expected, $actual)
    {
        $this->assertEquals($expected->id, $actual['id']);
        $this->assertEquals($expected->name, $actual['name']);
        $this->assertEquals($expected->description, $actual['description']);
        $this->assertEquals($expected->author_id, $actual['author_id']);
        $this->assertEquals($expected->created_at->getTimestamp(), $actual['created_at']);
        $this->assertEquals($expected->updated_at->getTimestamp(), $actual['updated_at']);
    }
}
