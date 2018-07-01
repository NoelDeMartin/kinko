<?php

namespace Kinko\GraphQL;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use GraphQL\Utils\AST;
use GraphQL\Error\Error;
use GraphQL\Type\Schema;
use GraphQL\Language\Parser;
use GraphQL\Utils\BuildSchema;
use GraphQL\Type\Introspection;
use GraphQL\Type\Definition\Type;
use GraphQL\Server\StandardServer;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\ObjectType;

use Illuminate\Support\Facades\App;

use Kinko\Models\Application;

class GraphQL
{
    public function parseGraphQLSchema($source, $validate = false)
    {
        $node = Parser::parse($source);

        if ($validate) {
            $schema = BuildSchema::build($node);

            if (!is_null($schema->getQueryType())) {
                throw new Error('Application schema must not contain internal types');
            }
            $schema->getConfig()->setQuery(new ObjectType(['name' => 'Query']));

            // TODO validate that only schema is defined, not queries, mutations or others

            $schema->assertValid();
        }

        return AST::toArray($node);
    }

    public function parseJsonSchema($source, $validate = false)
    {
        $schema = json_decode($source, true);

        if ($validate) {
            $node = AST::fromArray($schema);
            $internalTypes = Type::getInternalTypes() + Introspection::getTypes();

            foreach ($node->definitions as $definition) {
                switch ($definition->kind) {
                    case NodeKind::OBJECT_TYPE_DEFINITION:
                        if (isset($internalTypes[$definition->name->value])) {
                            throw new Error('Application schema must not contain internal types definition');
                        }
                        break;
                    case NodeKind::SCHEMA_DEFINITION:
                    case NodeKind::SCALAR_TYPE_DEFINITION:
                    case NodeKind::INTERFACE_TYPE_DEFINITION:
                    case NodeKind::ENUM_TYPE_DEFINITION:
                    case NodeKind::UNION_TYPE_DEFINITION:
                    case NodeKind::INPUT_OBJECT_TYPE_DEFINITION:
                    case NodeKind::DIRECTIVE_DEFINITION:
                        throw new Error('Application schema must only contain type definitions');
                }
            }
        }

        return $schema;
    }

    public function query(Application $application, ServerRequestInterface $request)
    {
        // TODO build CRUD queries similar to graphcool: https://gist.github.com/gc-codesnippets/cc487a35a39f59e6b7cb383734217050
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'echo' => [
                    'type' => Type::string(),
                    'args' => [
                        'message' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => function ($root, $args) {
                        return $root['prefix'] . $args['message'];
                    }
                ],
            ],
        ]);

        // TODO refactor to use only one constructor
        $schema = BuildSchema::build(AST::fromArray($application->schema));
        $schema->getConfig()->setQuery($queryType);
        $schema = new Schema($schema->getConfig());

        $rootValue = ['prefix' => 'Hello '];
        $server = new StandardServer(compact('schema', 'rootValue'));

        $response = App::make(ResponseInterface::class);
        return $server->processPsrRequest($request, $response, $response->getBody());
    }
}
