<?php

namespace Tests\Unit;

use Tests\TestCase;
use GraphQL\Error\Error;
use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
use Kinko\GraphQL\SchemaBuilder;
use Kinko\Support\Facades\GraphQL;

class GraphQLTests extends TestCase
{
    public function test_validate_schema()
    {
        $this->assertNotNull(GraphQL::parseGraphQLSchema(load_stub('schema.graphql'), true));
    }

    public function test_validate_schema_and_dont_allow_queries()
    {
        $this->expectException(Error::class, 'Application schema must not declare queries, mutations nor subscriptions');

        GraphQL::parseGraphQLSchema(load_stub('schema_with_query.graphql'), true);
    }

    public function test_validate_schema_and_force_ids()
    {
        $this->expectException(Error::class, 'Root types must define id field');

        GraphQL::parseGraphQLSchema(load_stub('schema_without_id.graphql'), true);
    }
}
