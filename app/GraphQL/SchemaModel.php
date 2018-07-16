<?php

namespace Kinko\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\Type;
use Kinko\GraphQL\Types\DateType;
use GraphQL\Language\AST\NodeKind;
use Illuminate\Support\Facades\Auth;
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

    public function getName()
    {
        return $this->type->name;
    }

    public function getPluralName()
    {
        return str_plural($this->getName());
    }

    public function getPrimaryKeyName()
    {
        return static::PRIMARY_KEY;
    }

    public function getFieldNames($typeName = null)
    {
        $fields = [];

        foreach ($this->type->astNode->fields as $field) {
            if (is_null($typeName)) {
                $fields[] = $field->name->value;
            } else {
                $type = $field->type;

                if ($type->kind === NodeKind::NON_NULL_TYPE) {
                    $type = $type->type;
                }

                if ($type->name->value === $typeName) {
                    $fields[] = $field->name->value;
                }
            }
        }

        return $fields;
    }

    public function buildQueries(&$queries)
    {
        $pluralName = $this->getPluralName();

        $queries['all' . $pluralName] = [
            'type' => Type::nonNull(Type::listOf(Type::nonNull($this->type))),
            'args' => [
                // TODO declare filters
            ],
            'resolve' => function ($root, $args) {
                // TODO apply filters
                return $this->query()->get()->map(function($result) {
                    return $this->schema->getDatabaseBridge()->convertResult($this, $result);
                });
            },
        ];
    }

    public function buildMutations(&$mutations)
    {
        $name = $this->getName();

        $mutations['create' . $name] = [
            'type' => Type::nonNull($this->type),
            'args' => $this->buildConstructorArguments(),
            'resolve' => function ($root, $args) {
                foreach ($this->autoFields as $field => $value) {
                    if ($field === static::CREATED_AT || $field === static::UPDATED_AT) {
                        $args[$field] = now();
                    } else if ($value === 'auth.id') {
                        if (Auth::guest()) {
                            throw new Error("Can't autofill $field without authentication.");
                        }

                        $args[$field] = Auth::id();
                    }
                }

                $data = $this->schema->getDatabaseBridge()->prepareValues($this, $args);

                $args[static::PRIMARY_KEY] = $this->query()->insertGetId($data);

                return $args;
            },
        ];
    }

    private function buildConstructorArguments()
    {
        $arguments = [];

        foreach ($this->type->astNode->fields as $field) {
            if ($field->name->value === static::PRIMARY_KEY) {
                continue;
            }

            if (count($field->directives) > 0) {
                // TODO define directive on schema
                // TODO decouple functionality (this method may be called more than once)
                foreach ($field->directives as $directive) {
                    if ($directive->name->value === 'auto') {
                        $this->defineAutoField($field, $directive);
                        continue 2;
                    }
                }
            }

            $required = false;
            $type = $field->type;

            if ($type->kind === NodeKind::NON_NULL_TYPE) {
                $required = true;
                $type = $type->type;
            }

            $typeName = $type->name->value;
            $type = $this->schema->getType($typeName);
            $arguments[$field->name->value] = $required ? Type::nonNull($type) : $type;
        }

        return $arguments;
    }

    private function defineAutoField($field, $directive)
    {
        $fieldName = $field->name->value;

        if ($fieldName === static::CREATED_AT || $fieldName === static::UPDATED_AT) {
            if ($field->type->kind !== NodeKind::NON_NULL_TYPE || $field->type->type->name->value !== DateType::NAME) {
                throw new Error("Field $fieldName with @auto directive must be of type non-null Date");
            }

            $this->autoFields[$fieldName] = true;
        } else {
            if (count($directive->arguments) !== 1 || $directive->arguments[0]->name->value !== 'value') {
                throw new Error("@auto directive format used for field $fieldName is not valid");
            }

            // TODO generalize
            if ($directive->arguments[0]->value->value !== 'auth.id') {
                throw new Error("@auto directive format used for field $fieldName is not valid");
            }

            $this->autoFields[$fieldName] = $directive->arguments[0]->value->value;
        }
    }

    private function query()
    {
        return $this->schema->getDatabaseBridge()->query($this);
    }
}
