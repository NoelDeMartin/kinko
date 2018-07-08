<?php

namespace Kinko\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Type\Definition\Type;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Utils\ASTDefinitionBuilder;
use GraphQL\Type\Definition\ObjectType;

class SchemaBuilder
{
    protected $ast;
    protected $db;

    protected $types = null;
    protected $customTypes = null;
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

        foreach ($this->customTypes as $type) {
            $fields = $type->getFields();
            if (
                !isset($fields['id']) ||
                !($fields['id']->getType() instanceof NonNull) ||
                !($fields['id']->getType()->getWrappedType() instanceof IDType)
            ) {
                throw new Error('Root types must define id field');
            }
        }

        if (!is_null($schema->getQueryType())) {
            throw new Error('Application schema must not declare queries');
        }
        $schema->getConfig()->setQuery(new ObjectType(['name' => 'Query']));

        $schema->assertValid();
    }

    public function build($withOperations = true)
    {
        $schema = BuildSchema::build($this->ast);

        $builtInTypes = Type::getAllBuiltInTypes();
        $this->types = $schema->getTypeMap();
        $this->customTypes = array_filter($this->types, function ($type) use ($builtInTypes) {
            return !isset($builtInTypes[$type->name]);
        });

        if ($withOperations) {
            $this->loadOperations();

            $schema->getConfig()->setQuery(new ObjectType([
                'name' => 'Query',
                'fields' => $this->queries,
            ]));
            $schema->getConfig()->setMutation(new ObjectType([
                'name' => 'Mutation',
                'fields' => $this->mutations,
            ]));

            $schema = new Schema($schema->getConfig());
        }

        return $schema;
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

            foreach ($this->customTypes as $type) {
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
