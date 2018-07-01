<?php

namespace Kinko\GraphQL;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use GraphQL\Utils\AST;
use GraphQL\Language\Parser;
use GraphQL\Server\StandardServer;

use Illuminate\Support\Facades\App;

use Kinko\Models\Application;

class GraphQL
{
    public function parseGraphQLSchema($source, $validate = false)
    {
        $ast = Parser::parse($source);

        if ($validate) {
            $builder = new SchemaBuilder($ast);
            $builder->validate();
        }

        return AST::toArray($ast);
    }

    public function parseJsonSchema($source, $validate = false)
    {
        $schema = json_decode($source, true);

        if ($validate) {
            $ast = AST::fromArray($schema);

            $builder = new SchemaBuilder($ast);
            $builder->validate();
        }

        return $schema;
    }

    public function query(Application $application, ServerRequestInterface $request)
    {
        $ast = AST::fromArray($application->schema);
        $db = App::make(GraphQLDatabaseBridge::class);
        $builder = new SchemaBuilder($ast, $db);
        $server = new StandardServer([
            'schema' => $builder->build(),
            'rootValue' => [],
        ]);
        $response = App::make(ResponseInterface::class);
        return $server->processPsrRequest($request, $response, $response->getBody());
    }
}
