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
    // TODO move $model to constructor

    public function create(SchemaModel $model, $args)
    {
        $id = $this->query($model)->insertGetId($this->prepareDatabaseValues($model, $args));

        $args[$model->getPrimaryKeyName()] = $id;

        return $args;
    }

    public function retrieve(SchemaModel $model, array $restrictions)
    {
        $query = $this->query($model);

        if (isset($restrictions['filter'])) {
            $this->applyFilter($model, $query, $restrictions['filter']);
        }

        if (isset($restrictions['orderBy'])) {
            // TODO
        }

        if (isset($restrictions['limit'])) {
            $query->limit($restrictions['limit']);
        }

        if (isset($restrictions['offset'])) {
            $query->offset($restrictions['offset']);
        }

        return $query->get()->map(function ($result) use ($model) {
            return $this->convertResult($model, $result);
        });
    }

    public function update(SchemaModel $model, $id, $args)
    {
        if (!empty($args)) {
            $updated = [];
            $removed = [];

            foreach ($this->prepareDatabaseValues($model, $args) as $key => $value) {
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

    public function delete(SchemaModel $model, $id)
    {
        $this->query($model)->where('_id', MongoDB::key($id))->delete();
    }

    private function query(SchemaModel $model)
    {
        $collection = 'store-' . strtolower($model->getPluralName());
        return DB::collection($collection);
    }

    private function applyFilter(SchemaModel $model, $query, $filter)
    {
        if (isset($filter['AND'])) {
            // TODO
        } else if (isset($filter['OR'])) {
            // TODO
        } else if (isset($filter['operation'])) {
            // TODO
        } else {
            $query->where(
                $this->getDatabaseFieldName($model, $filter['field']),
                $this->prepareDatabaseValue($model, $filter['field'], $filter['value'])
            );
        }
    }

    private function getDatabaseFieldName(SchemaModel $model, $field)
    {
        return $field === $model->getPrimaryKeyName()
            ? '_id'
            : $field;
    }

    private function prepareDatabaseValues(SchemaModel $model, $values)
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

    private function prepareDatabaseValue(SchemaModel $model, $field, $value)
    {
        switch ($model->getFieldTypeName($field)) {
            case Type::ID:
                return MongoDB::key($value);
            case DateType::NAME:
                return MongoDB::date($value);
            default:
                return $value;
        }
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
}
