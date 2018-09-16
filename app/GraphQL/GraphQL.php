<?php

namespace Kinko\GraphQL;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use GraphQL\Utils\AST;
use GraphQL\Language\Parser;
use GraphQL\Server\StandardServer;

use Illuminate\Support\Facades\App;

use Kinko\Models\Client;

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

    public function query(Client $client, ServerRequestInterface $request)
    {
        $ast = AST::fromArray($client->schema);
        $databaseProvider = App::make(GraphQLDatabaseProvider::class);
        $schema = new Schema($ast, $databaseProvider);
        $server = new StandardServer([
            'schema' => $schema->build(),
            'rootValue' => [],
        ]);
        $response = App::make(ResponseInterface::class);
        return $server->processPsrRequest($request, $response, $response->getBody());
    }
}
