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
            $schema = new Schema($ast);
            $schema->validate();
        }

        return AST::toArray($ast);
    }

    public function parseJsonSchema($source, $validate = false)
    {
        $schemaDefinition = json_decode($source, true);

        if ($validate) {
            $ast = AST::fromArray($schemaDefinition);

            $schema = new Schema($ast);
            $schema->validate();
        }

        return $schemaDefinition;
    }

    public function query(Application $application, ServerRequestInterface $request)
    {
        $ast = AST::fromArray($application->schema);
        $db = App::make(GraphQLDatabaseBridge::class);
        $schema = new Schema($ast, $db);
        $server = new StandardServer([
            'schema' => $schema->build(),
            'rootValue' => [],
        ]);
        $response = App::make(ResponseInterface::class);
        return $server->processPsrRequest($request, $response, $response->getBody());
    }
}
