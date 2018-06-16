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
    /**
     * Parses GraphQL Schema into ApplicationSchema.
     */
    public function parseGraphQLSchema($source, $validate = false)
    {
        $schema = BuildSchema::build($source);

        if ($validate) {
            if (!is_null($schema->getQueryType())) {
                throw new Error('Application schema must not contain internal types');
            }
            $schema->getConfig()->setQuery(new ObjectType(['name' => 'Query']));

            // TODO validate that only schema is defined, not queries, mutations or others

            $schema->assertValid();
        }

        return $this->convertToApplicationSchema($schema);
    }

    /**
     * Parses Json into ApplicationSchema.
     */
    public function parseJson($source, $validate = false)
    {
        $schema = json_decode($source, true);
        $applicationSchema = new ApplicationSchema;
        $internalTypes = Type::getInternalTypes() + Introspection::getTypes();

        foreach ($schema as $typeName => $type) {
            $applicationType = new ApplicationType;

            if ($validate && isset($internalTypes[$typeName])) {
                throw new Error('Application schema must not contain internal types');
            }

            foreach ($type as $fieldName => $field) {
                $applicationType->addField($fieldName, $field['type'], $field['required']);
            }

            $applicationSchema->addType($typeName, $applicationType);
        }

        return $applicationSchema;
    }

    private function convertToApplicationSchema(Schema $schema)
    {
        $applicationSchema = new ApplicationSchema;
        $types = $schema->getTypeMap();
        $internalTypes = Type::getInternalTypes() + Introspection::getTypes();

        foreach ($types as $name => $type) {
            if (isset($internalTypes[$name])) {
                continue;
            }

            $applicationSchema->addType($name, $this->convertToApplicationType($type));
        }

        return $applicationSchema;
    }

    private function convertToApplicationType(ObjectType $type)
    {
        $applicationType = new ApplicationType;
        $fields = $type->getFields();

        foreach ($fields as $field) {
            $fieldType = $field->getType();
            $required = false;

            if ($fieldType instanceof NonNull) {
                $required = true;
                $fieldType = $fieldType->getWrappedType();
            }

            $applicationType->addField($field->name, $fieldType->name, $required);
        }

        return $applicationType;
    }
}
