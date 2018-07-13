<?php

namespace Kinko\GraphQL;

use GraphQL\Type\Definition\Type;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Type\Definition\ObjectType;

class SchemaModel
{
    const PRIMARY_KEY = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $schema;
    protected $type;

    protected $autoFields = [];

    public function __construct(Schema $schema, ObjectType $type)
    {
        $this->schema = $schema;
        $this->type = $type;
    }

    public function buildQueries(&$queries)
    {
        $singular = $this->type->name;
        $plural = str_plural($singular);

        $queries['all' . $plural] = [
            'type' => Type::nonNull(Type::listOf(Type::nonNull($this->type))),
            'args' => [
                // TODO declare filters
            ],
            'resolve' => function ($root, $args) {
                // TODO apply filters
                return $this->query()->get()->map(function($result) {
                    return $this->schema->getDatabaseBridge()->convertResult($result);
                });
            },
        ];
    }

    public function buildMutations(&$mutations)
    {
        $singular = $this->type->name;
        $plural = str_plural($singular);

        $mutations['create' . $singular] = [
            'type' => Type::nonNull($this->type),
            'args' => $this->buildTypeConstructorArguments($this->type),
            'resolve' => function ($root, $args) {
                if ($this->isAuto(static::CREATED_AT)) {
                    $args[static::CREATED_AT] = now();
                }

                if ($this->isAuto(static::UPDATED_AT)) {
                    $args[static::UPDATED_AT] = now();
                }

                $args[static::PRIMARY_KEY] = $this->query()->insertGetId($args);

                return $args;
            },
        ];
    }

    private function isAuto($field)
    {
        return in_array($field, $this->autoFields);
    }

    private function buildTypeConstructorArguments($type)
    {
        $arguments = [];

        foreach ($type->astNode->fields as $field) {
            if ($field->name->value === static::PRIMARY_KEY) {
                continue;
            }

            $required = false;
            $type = $field->type;

            if ($type->kind === NodeKind::NON_NULL_TYPE) {
                $required = true;
                $type = $type->type;
            }

            if (count($field->directives) > 0) {
                // TODO define directive on schema
                foreach ($field->directives as $directive) {
                    if ($directive->name->value === 'auto') {
                        $this->autoFields[] = $field->name->value;
                        continue 2;
                    }
                }
            }

            $typeName = $type->name->value;
            $type = $this->schema->getType($typeName);
            $arguments[$field->name->value] = $required ? Type::nonNull($type) : $type;
        }

        return $arguments;
    }

    private function query()
    {
        return $this->schema->getDatabaseBridge()->query($this->type);
    }
}