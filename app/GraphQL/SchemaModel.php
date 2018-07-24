<?php

namespace Kinko\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\Type;
use Kinko\GraphQL\Types\DateType;
use GraphQL\Language\AST\NodeKind;
use Illuminate\Support\Facades\Auth;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;

class SchemaModel
{
    const PRIMARY_KEY = 'id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const TYPE_FILTER = 'Filter';
    const TYPE_ORDER_BY = 'OrderBy';

    protected $schema;
    protected $type;
    protected $autoFields;

    public function __construct(Schema $schema, ObjectType $type)
    {
        $this->schema = $schema;
        $this->type = $type;

        $this->autoFields = [];
        foreach ($this->type->astNode->fields as $field) {
            if ($field->name->value === static::PRIMARY_KEY) {
                continue;
            }

            if (count($field->directives) > 0) {
                // TODO define directive on schema
                foreach ($field->directives as $directive) {
                    if ($directive->name->value === 'auto') {
                        $this->defineAutoField($field, $directive);
                        continue 2;
                    }
                }
            }
        }
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

    public function getFieldTypeName($fieldName)
    {
        foreach ($this->type->astNode->fields as $field) {
            if ($field->name->value === $fieldName) {
                $type = $field->type;

                if ($type->kind === NodeKind::NON_NULL_TYPE) {
                    $type = $type->type;
                }

                return $type->name->value;
            }
        }

        return null;
    }

    public function buildTypes(&$types)
    {
        $types[static::TYPE_FILTER] = new InputObjectType([
            'name' => 'Filter',
            'fields' => function() {
                return [
                    'AND' => [
                        'type' => Type::listOf(Type::nonNull($this->schema->getType(static::TYPE_FILTER))),
                    ],
                    'OR' => [
                        'type' => Type::listOf(Type::nonNull($this->schema->getType(static::TYPE_FILTER))),
                    ],
                    'field' => [ 'type' => Type::string() ],
                    'operation' => [ 'type' => Type::string() ], // TODO enum Operation
                    'value' => [ 'type' => Type::string() ], // TODO any
                ];
            },
        ]);

        $types[static::TYPE_ORDER_BY] = new InputObjectType([
            'name' => 'OrderBy',
            'fields' => function () {
                return [
                    'AND' => [
                        'type' => Type::listOf(Type::nonNull($this->schema->getType(static::TYPE_ORDER_BY))),
                    ],
                    'field' => [ 'type' => Type::string() ],
                    'direction' => [ 'type' => Type::string() ], // TODO enum OrderByDirection
                ];
            },
        ]);
    }

    public function buildQueries(&$queries)
    {
        $name = $this->getName();
        $pluralName = $this->getPluralName();

        $queries['get' . $name] = [
            'type' => $this->type,
            'args' => [
                static::PRIMARY_KEY => Type::id(),
            ],
            'resolve' => function ($root, $args) {
                $results = $this->schema->getDatabaseBridge()->retrieve($this, [
                    'filter' => [
                        'field' => static::PRIMARY_KEY,
                        'value' => $args[static::PRIMARY_KEY],
                    ],
                    'limit' => 1,
                ]);

                return count($results) > 0 ? $results[0] : null;
            },
        ];

        $queries['get' . $pluralName] = [
            'type' => Type::nonNull(Type::listOf(Type::nonNull($this->type))),
            'args' => [
                'filter' => $this->schema->getType(static::TYPE_FILTER),
                'orderBy' => $this->schema->getType(static::TYPE_ORDER_BY),
                'limit' => Type::int(),
                'offset' => Type::int(),
            ],
            'resolve' => function ($root, $args) {
                return $this->schema->getDatabaseBridge()->retrieve($this, $args);
            },
        ];
    }

    public function buildMutations(&$mutations)
    {
        $name = $this->getName();

        $mutations['create' . $name] = [
            'type' => Type::nonNull($this->type),
            'args' => $this->buildTypeArguments(),
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

                return $this->schema->getDatabaseBridge()->create($this, $args);
            },
        ];

        $mutations['update' . $name] = [
            'type' => Type::nonNull($this->type),
            'args' => array_merge($this->buildTypeArguments(false), [
                static::PRIMARY_KEY => Type::nonNull(Type::id()),
            ]),
            'resolve' => function ($root, $args) {
                $id = $args[static::PRIMARY_KEY];
                unset($args[static::PRIMARY_KEY]);

                if ($this->isAuto(static::UPDATED_AT)) {
                    $args[static::UPDATED_AT] = now();
                }

                return $this->schema->getDatabaseBridge()->update($this, $id, $args);
            },
        ];

        $mutations['delete' . $name] = [
            'type' => Type::boolean(),
            'args' => [
                static::PRIMARY_KEY => Type::nonNull(Type::id()),
            ],
            'resolve' => function ($root, $args) {
                $this->schema->getDatabaseBridge()->delete($this, $args[static::PRIMARY_KEY]);

                return true;
            },
        ];
    }

    private function isAuto($field)
    {
        return isset($this->autoFields[$field]);
    }

    private function buildTypeArguments($maintainRequired = true)
    {
        $arguments = [];

        foreach ($this->type->astNode->fields as $field) {
            $fieldName = $field->name->value;

            if ($fieldName === static::PRIMARY_KEY || $this->isAuto($fieldName)) {
                continue;
            }

            $required = false;
            $type = $field->type;

            if ($type->kind === NodeKind::NON_NULL_TYPE) {
                $required = true;
                $type = $type->type;
            }

            $typeName = $type->name->value;
            $type = $this->schema->getType($typeName);
            $arguments[$fieldName] = ($required && $maintainRequired) ? Type::nonNull($type) : $type;
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
}
