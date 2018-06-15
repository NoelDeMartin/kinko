<?php

namespace Kinko\GraphQL;

use GraphQL\Type\Schema;
use GraphQL\Error\Error;
use GraphQL\Utils\BuildSchema;
use GraphQL\Type\Introspection;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;

class GraphQL
{
    public function parseSchema($source, $validate = false)
    {
        $schema = BuildSchema::build($source);

        if ($validate) {
            if (!is_null($schema->getQueryType())) {
                throw new Error("Schema definitions don't accept Query type definitions");
            }
            $schema->getConfig()->setQuery(new ObjectType(['name' => 'Query']));

            // TODO validate that only schema is defined, not queries, mutations or others

            $schema->assertValid();
        }

        return $schema;
    }

    public function serializeSchema(Schema $schema)
    {
        $types = $schema->getTypeMap();
        $internalTypes = Type::getInternalTypes() + Introspection::getTypes();
        $serializedSchema = [];

        foreach ($types as $name => $type) {
            if (isset($internalTypes[$name])) {
                continue;
            }

            $serializedSchema[$name] = $this->serializeType($type);
        }

        return $serializedSchema;
    }

    private function serializeType(ObjectType $type)
    {
        $fields = $type->getFields();
        $definition = [];

        foreach ($fields as $field) {
            $fieldDefinition = [];
            $fieldType = $field->getType();

            if ($fieldType instanceof NonNull) {
                $fieldDefinition['required'] = true;
                $fieldType = $fieldType->getWrappedType();
            } else {
                $fieldDefinition['required'] = false;
            }

            $fieldDefinition['type'] = $fieldType->name;

            $definition[$field->name] = $fieldDefinition;
        }

        return $definition;
    }
}
