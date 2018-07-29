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

        $response = $this->login()->graphql('{tasks: getTasks{id}}');

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['tasks']]);

        $this->assertCount($tasksCount, $response->json('data.tasks'));
    }

    public function test_query_find_one()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $task = $this->createTasks(random_int(5, 10))->random();

        $response = $this->login()->graphql(
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
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $tasks = $this->createTasks(random_int(5, 10))->random(random_int(2, 4));

        $name = $this->faker->unique()->sentence;
        foreach ($tasks as $task) {
            $task->name = $name;
            DB::collection('store-tasks')
                ->where('_id', MongoDB::key($task->id))
                ->update(compact('name'));
        }

        $response = $this->login()->graphql(
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
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $this->createTasks(1, ['name' => 'a', 'description' => 'c']);
        $this->createTasks(1, ['name' => 'b', 'description' => 'a']);
        $tasks = collect([
            $this->createTasks(1, ['name' => 'a', 'description' => 'a']),
            $this->createTasks(1, ['name' => 'a', 'description' => 'b']),
        ]);

        $response = $this->login()->graphql(
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
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $tasks = collect([
            $this->createTasks(1, ['name' => 'a']),
            $this->createTasks(1, ['name' => 'b']),
            $this->createTasks(1, ['name' => 'c']),
        ])->reverse()->values();

        $response = $this->login()->graphql(
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
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $tasks = collect([
            $this->createTasks(1, ['name' => 'a', 'description' => 'b']),
            $this->createTasks(1, ['name' => 'a', 'description' => 'a']),
            $this->createTasks(1, ['name' => 'b', 'description' => 'c']),
            $this->createTasks(1, ['name' => 'c', 'description' => 'c']),
        ])->reverse()->values();

        $response = $this->login()->graphql(
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
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $tasks = $this->createTasks(random_int(5, 10));
        $limit = intval($tasks->count() / 2);

        $response = $this->login()->graphql(
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
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $tasks = $this->createTasks(random_int(5, 10));
        $offset = intval($tasks->count() / 2);

        $response = $this->login()->graphql(
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
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['task' => ['id', 'name', 'description', 'author_id', 'created_at', 'updated_at']]]);

        $this->assertEquals(1, DB::collection('store-tasks')->count());
        $this->assertEquals($name, $response->json('data.task.name'));
        $this->assertEquals($description, $response->json('data.task.description'));
        $this->assertEquals($user->id, $response->json('data.task.author_id'));
        $this->assertEquals($now->getTimestamp(), $response->json('data.task.created_at'));
        $this->assertEquals($now->getTImestamp(), $response->json('data.task.updated_at'));
    }

    public function test_mutation_update_one()
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

        $document = DB::collection('store-tasks')->first();
        $this->assertArrayHasKey('name', $document);
        $this->assertArrayNotHasKey('description', $document);
        $this->assertArrayHasKey('created_at', $document);
        $this->assertArrayHasKey('updated_at', $document);
    }

    public function test_mutation_update_many_and_return_count()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $user = factory(User::class)->create();
        $originalName = $this->faker->sentence;
        $updatedName = $this->faker->sentence;

        $tasks = $this->createTasks(random_int(5, 10));
        $mutatedTasks = $this->createTasks(random_int(5, 10), [
            'name' => $originalName,
        ]);

        $response = $this->login($user)->graphql(
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
            $updatedTask = DB::collection('store-tasks')->where('_id', MongoDB::key($task->id))->first();
            $this->assertEquals($task->name, $updatedTask['name']);
            $this->assertEquals($task->description, $updatedTask['description']);
            $this->assertEquals($task->author_id, $updatedTask['author_id']);
            $this->assertEquals($task->id, $updatedTask['_id']);
            $this->assertEquals($task->created_at->getTimestamp(), $updatedTask['created_at']->toDateTime()->getTimestamp());
            $this->assertEquals($task->updated_at->getTimestamp(), $updatedTask['updated_at']->toDateTime()->getTimestamp());
        }

        foreach ($mutatedTasks as $task) {
            $updatedTask = DB::collection('store-tasks')->where('_id', MongoDB::key($task->id))->first();
            $this->assertEquals($updatedName, $updatedTask['name']);
            $this->assertArrayNotHasKey('description', $updatedTask);
            $this->assertEquals($task->author_id, $updatedTask['author_id']);
            $this->assertEquals($task->created_at->getTimestamp(), $updatedTask['created_at']->toDateTime()->getTimestamp());
            $this->assertEquals($task->updated_at->getTimestamp(), $updatedTask['updated_at']->toDateTime()->getTimestamp());
        }
    }

    public function test_mutation_update_many_and_return_objects()
    {
        factory(Application::class)->create([
            'schema' => load_stub('schema.json'),
        ]);

        $user = factory(User::class)->create();
        $originalName = $this->faker->sentence;
        $updatedName = $this->faker->sentence;

        $tasks = $this->createTasks(random_int(5, 10));
        $mutatedTasks = $this->createTasks(random_int(5, 10), [
            'name' => $originalName,
        ]);

        $response = $this->login($user)->graphql(
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

    public function test_mutation_delete()
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

        $response = $this->login($user)->graphql(
            "mutation {
                result: deleteTask(id: \"$id\")
            }"
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['result']]);

        $this->assertEquals(0, DB::collection('store-tasks')->count());
        $this->assertTrue($response->json('data.result'));
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
            $task['id'] = (string) DB::collection('store-tasks')->insertGetId($task);
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
