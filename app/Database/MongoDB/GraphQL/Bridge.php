<?php

namespace Kinko\Database\MongoDB\GraphQL;

use Kinko\GraphQL\SchemaModel;
use GraphQL\Type\Definition\Type;
use Kinko\GraphQL\Types\DateType;
use Illuminate\Support\Facades\DB;
use Kinko\Support\Facades\MongoDB;
use Kinko\GraphQL\GraphQLDatabaseBridge;

class Bridge implements GraphQLDatabaseBridge
{
    public function query(SchemaModel $model)
    {
        $collection = 'store-' . strtolower($model->getPluralName());
        return DB::collection($collection);
    }

    public function prepareValues(SchemaModel $model, $values)
    {
        foreach ($model->getFieldNames(Type::ID) as $field) {
            if (isset($values[$field])) {
                $values[$field] = MongoDB::key($values[$field], true);
            }
        }

        foreach ($model->getFieldNames(DateType::NAME) as $field) {
            if (isset($values[$field])) {
                $values[$field] = MongoDB::date($values[$field], true);
            }
        }

        return $values;
    }

    public function convertResult(SchemaModel $model, $document)
    {
        $document = MongoDB::convertDocument($document);

        if (isset($document['_id'])) {
            $document[$model->getPrimaryKeyName()] = $document['_id'];
            unset($document['_id']);
        }

        return $document;
    }
}
