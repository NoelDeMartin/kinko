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
    public function create(SchemaModel $model, $args)
    {
        $id = $this->query($model)->insertGetId($this->prepareValues($model, $args));

        $args[$model->getPrimaryKeyName()] = $id;

        return $args;
    }

    public function retrieve(SchemaModel $model)
    {
        return $this->query($model)->get()->map(function ($result) use ($model) {
            return $this->convertResult($model, $result);
        });
    }

    public function update(SchemaModel $model, $id, $args)
    {
        if (!empty($args)) {
            $updated = [];
            $removed = [];

            foreach ($this->prepareValues($model, $args) as $key => $value) {
                if (is_null($value)) {
                    $removed[] = $key;
                } else {
                    $updated[$key] = $value;
                }
            }

            $this->query($model)->where('_id', MongoDB::key($id))->update($updated, $removed);
        }

        $result = $this->query($model)->where('_id', MongoDB::key($id))->first();

        return $this->convertResult($model, $result);
    }

    private function prepareValues(SchemaModel $model, $values)
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

    private function convertResult(SchemaModel $model, $document)
    {
        $document = MongoDB::convertDocument($document);

        if (isset($document['_id'])) {
            $document[$model->getPrimaryKeyName()] = $document['_id'];
            unset($document['_id']);
        }

        return $document;
    }

    private function query(SchemaModel $model)
    {
        $collection = 'store-' . strtolower($model->getPluralName());
        return DB::collection($collection);
    }
}
