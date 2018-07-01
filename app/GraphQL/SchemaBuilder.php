<?php

namespace Kinko\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Utils\ASTDefinitionBuilder;
use GraphQL\Type\Definition\ObjectType;

class SchemaBuilder
{
    protected $ast;
    protected $db;

    protected $types = null;
    protected $queries = null;
    protected $mutations = null;

    public function __construct($ast, $db = null)
    {
        $this->ast = $ast;
        $this->db = $db;
    }

    public function validate()
    {
        $schema = $this->build(false);

        if (!is_null($schema->getQueryType())) {
            throw new Error('Application schema must not contain internal types');
        }

        $schema->getConfig()->setQuery(new ObjectType(['name' => 'Query']));

        // TODO validate that only schema is defined, not queries, mutations nor others

        $schema->assertValid();
    }

    public function build($withOperations = true)
    {
        $this->loadTypes();

        $config = ['types' => $this->types];

        if ($withOperations) {
            $this->loadOperations();

            $config['query'] = new ObjectType([
                'name' => 'Query',
                'fields' => $this->queries,
            ]);
            $config['mutation'] = new ObjectType([
                'name' => 'Mutation',
                'fields' => $this->mutations,
            ]);
        }

        return new Schema($config);
    }

    private function loadTypes()
    {
        if (is_null($this->types)) {
            $this->types = [];

            $typeDefintionsMap = [];
            foreach ($this->ast->definitions as $definition) {
                switch ($definition->kind) {
                    case NodeKind::OBJECT_TYPE_DEFINITION:
                        $typeDefintionsMap[$definition->name->value] = $definition;
                        break;
                }
            }

            $options = [];
            $resolveType = function ($typeName) {
                throw new Error("Type $typeName not found in document.");
            };
            $defintionBuilder = new ASTDefinitionBuilder($typeDefintionsMap, $options, $resolveType);
            $this->types = array_map(function ($definition) use ($defintionBuilder) {
                return $defintionBuilder->buildType($definition);
            }, $typeDefintionsMap);
        }
    }

    private function loadOperations()
    {
        if (is_null($this->queries) && is_null($this->mutations)) {
            $this->queries = [
                'ping' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => function ($root, $args) {
                        return 'pong';
                    },
                ],
            ];
            $this->mutations = [];

            foreach ($this->types as $type) {
                $this->addTypeOperations($type);
            }
        }
    }

    private function addTypeOperations($type)
    {
        $singular = $type->name;
        $plural = str_plural($singular);

        // TODO build CRUD queries similar to graphcool: https://gist.github.com/gc-codesnippets/cc487a35a39f59e6b7cb383734217050
        $this->queries['all' . $plural] = [
            'type' => Type::nonNull(Type::listOf(Type::nonNull($type))),
            'args' => [
                // TODO declare filters
            ],
            'resolve' => function ($root, $args) use ($type) {
                // TODO apply filters
                return $this->db->query($type)->get()->map(function($result) {
                    return $this->db->convertResult($result);
                });
            },
        ];

        $this->mutations['create' . $singular] = [
            'type' => Type::nonNull($type),
            'args' => $this->buildTypeArgs($type),
            'resolve' => function ($root, $args) use ($type) {
                $args['id'] = $this->db->query($type)->insertGetId($args);

                return $args;
            },
        ];
    }

    private function buildTypeArgs($type)
    {
        $args = [];
        $internalTypes = Type::getInternalTypes();

        foreach ($type->astNode->fields as $field) {
            $required = false;
            $type = $field->type;

            if ($type->kind === NodeKind::NON_NULL_TYPE) {
                $required = true;
                $type = $type->type;
            }

            $typeName = $type->name->value;

            if ($typeName === Type::ID) {
                // TODO don't skip for foreign keys, only for primary keys
                continue;
            } elseif (isset($internalTypes[$typeName])) {
                $type = $internalTypes[$typeName];
            } else {
                $type = $this->types[$typeName];
            }

            $args[$field->name->value] = $required ? Type::nonNull($type) : $type;
        }

        return $args;
    }
}
