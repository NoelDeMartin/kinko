<?php

namespace Kinko\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use GraphQL\Type\Definition\Type;
use Kinko\GraphQL\Types\DateType;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Directive;
use GraphQL\Utils\ASTDefinitionBuilder;
use GraphQL\Type\Definition\ObjectType;

class SchemaBuilder
{
    protected $ast;
    protected $db;

    protected $types = null;
    protected $customTypes = null;
    protected $customScalarTypes = null;

    public function __construct($ast, $db = null)
    {
        $this->ast = $ast;
        $this->db = $db;
    }

    public function build($validate = false)
    {
        $config = [];
        $typeDefinitions = [];
        foreach ($this->ast->definitions as $definition) {
            switch ($definition->kind) {
                case NodeKind::SCHEMA_DEFINITION:
                    if (isset($config['astNode'])) {
                        throw new Error('Must provide only one schema definition.');
                    }
                    $config['astNode'] = $definition;
                    break;
                case NodeKind::SCALAR_TYPE_DEFINITION:
                case NodeKind::OBJECT_TYPE_DEFINITION:
                case NodeKind::INTERFACE_TYPE_DEFINITION:
                case NodeKind::ENUM_TYPE_DEFINITION:
                case NodeKind::UNION_TYPE_DEFINITION:
                case NodeKind::INPUT_OBJECT_TYPE_DEFINITION:
                    $name = $definition->name->value;
                    if (isset($typeDefinitions[$name])) {
                        throw new Error("Type [$name] was defined more than once.");
                    }
                    $typeDefinitions[$name] = $definition;
                    break;
                case NodeKind::DIRECTIVE_DEFINITION:
                    throw new Error('Directive definitions are not supported.');
                    break;
            }
        }

        $this->customScalarTypes = $this->buildCustomScalarTypes();
        $this->customTypes = $this->buildCustomTypes($typeDefinitions);
        $this->types = array_merge($this->customScalarTypes, $this->customTypes, Type::getAllBuiltInTypes());

        $config['typesLoader'] = function ($name) {
            return $this->types[$name];
        };

        $config = array_merge($config, $this->buildOperations());

        if ($validate) {
            $this->validateDefinitions($config, $typeDefinitions);

            $schema = new Schema($config);
            $schema->assertValid();

            return $schema;
        } else {
            return new Schema($config);
        }
    }

    private function buildCustomScalarTypes()
    {
        return [
            'Date' => new DateType,
        ];
    }

    private function buildCustomTypes($typeDefinitions)
    {
        $customTypes = [];

        $definitionBuilder = new ASTDefinitionBuilder(
            $typeDefinitions,
            [],
            function ($name) {
                if (isset($this->customScalarTypes[$name])) {
                    return $this->customScalarTypes[$name];
                }

                throw new Error("Type [$name] not found in document.");
            }
        );

        foreach ($typeDefinitions as $name => $definition) {
            $customTypes[$name] = $definitionBuilder->buildType($name);
        }

        return $customTypes;
    }

    private function buildOperations()
    {
        $queries = [
            'ping' => [
                'type' => Type::nonNull(Type::string()),
                'resolve' => function ($root, $args) {
                    return 'pong';
                },
            ],
        ];
        $mutations = [];

        foreach ($this->customTypes as $type) {
            $this->buildTypeOperations($type, $queries, $mutations);
        }

        return [
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => $queries,
            ]),
            'mutation' => new ObjectType([
                'name' => 'Mutation',
                'fields' => $mutations,
            ]),
        ];
    }

    private function buildTypeOperations($type, &$queries, &$mutations)
    {
        $singular = $type->name;
        $plural = str_plural($singular);

        // TODO build CRUD queries similar to graphcool: https://gist.github.com/gc-codesnippets/cc487a35a39f59e6b7cb383734217050
        $queries['all' . $plural] = [
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

        $mutations['create' . $singular] = [
            'type' => Type::nonNull($type),
            'args' => $this->buildTypeConstructorArguments($type),
            'resolve' => function ($root, $args) use ($type) {
                $args['id'] = $this->db->query($type)->insertGetId($args);

                return $args;
            },
        ];
    }

    private function buildTypeConstructorArguments($type)
    {
        $arguments = [];

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
            } else {
                $type = $this->types[$typeName];
            }

            $arguments[$field->name->value] = $required ? Type::nonNull($type) : $type;
        }

        return $arguments;
    }

    private function validateDefinitions($config, $typeDefinitions)
    {
        if (
            $this->schemaDefinesOperations($config) ||
            isset($typeDefinitions['Query']) ||
            isset($typeDefinitions['Mutation']) ||
            isset($typeDefinitions['Subscription'])
        ) {
            throw new Error('Application schema must not declare queries, mutations nor subscriptions');
        }

        foreach ($this->customTypes as $type) {
            $fields = $type->getFields();
            if (!isset($fields['id']) ||
                !($fields['id']->getType() instanceof NonNull) ||
                !($fields['id']->getType()->getWrappedType() instanceof IDType)) {
                throw new Error('Root types must define id field');
            }
        }
    }

    private function schemaDefinesOperations($config)
    {
        // TODO check $config['astNode]

        return false;
    }
}
