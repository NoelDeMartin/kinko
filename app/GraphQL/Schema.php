<?php

namespace Kinko\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Utils\BuildSchema;
use GraphQL\Type\Definition\Type;
use Kinko\GraphQL\Types\DateType;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Directive;
use GraphQL\Utils\ASTDefinitionBuilder;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema as GraphQLSchema;

class Schema
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

    public function validate()
    {
        $parsedAST = $this->parseAST();

        $this->validateDefinitions(
            $parsedAST['schemaConfig'],
            $parsedAST['typeDefinitions']
        );

        $schema = new GraphQLSchema($parsedAST['schemaConfig']);
        $schema->assertValid();

        return $schema;
    }

    public function build()
    {
        $parsedAST = $this->parseAST();

        return new GraphQLSchema($parsedAST['schemaConfig']);
    }

    public function getType($name)
    {
        return isset($this->types[$name]) ? $this->types[$name] : null;
    }

    public function getDatabaseBridge()
    {
        return $this->db;
    }

    private function parseAST()
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

        return [
            'schemaConfig' => array_merge($config, $this->buildOperations()),
            'typeDefinitions' => $typeDefinitions,
        ];
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
        $model = new SchemaModel($this, $type);
        $model->buildQueries($queries);
        $model->buildMutations($mutations);
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
            if (!isset($fields[SchemaModel::PRIMARY_KEY]) ||
                !($fields[SchemaModel::PRIMARY_KEY]->getType() instanceof NonNull) ||
                !($fields[SchemaModel::PRIMARY_KEY]->getType()->getWrappedType() instanceof IDType)) {
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
