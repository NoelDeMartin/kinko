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
    protected $model;

    public function __construct(SchemaModel $model)
    {
        $this->model = $model;
    }

    public function create(array $args)
    {
        $id = $this->query()->insertGetId($this->prepareDatabaseValues($args));

        $args[$this->model->getPrimaryKeyName()] = $id;

        return $args;
    }

    public function retrieve(array $restrictions)
    {
        $query = $this->query();

        if (isset($restrictions['filter'])) {
            $this->applyFilter($query, $restrictions['filter']);
        }

        if (isset($restrictions['orderBy'])) {
            $this->applyOrderBy($query, $restrictions['orderBy']);
        }

        if (isset($restrictions['limit'])) {
            $query->limit($restrictions['limit']);
        }

        if (isset($restrictions['offset'])) {
            $query->offset($restrictions['offset']);
        }

        return $query->get()->map(function ($result) {
            return $this->convertResult($result);
        });
    }

    public function update(array $filter, array $args, bool $returnObjects)
    {
        if (!empty($args)) {
            $updated = [];
            $removed = [];

            foreach ($this->prepareDatabaseValues($args) as $key => $value) {
                if (is_null($value)) {
                    $removed[] = $key;
                } else {
                    $updated[$key] = $value;
                }
            }

            $query = $this->query();

            if (!empty($filter)) {
                $this->applyFilter($query, $filter);
            }

            if ($returnObjects) {
                $updatedRecordsIds = $query->pluck('_id');
            }

            $count = $query->update($updated, $removed);

            if ($returnObjects) {
                return $this
                    ->query()
                    ->whereIn('_id', $updatedRecordsIds->all())
                    ->get()
                    ->map(function ($result) {
                        return $this->convertResult($result);
                    });
            } else {
                return $count;
            }
        } else {
            return $returnObjects ? [] : 0;
        }
    }

    public function delete(array $filter, bool $returnIds)
    {
        $query = $this->query();

        if (!empty($filter)) {
            $this->applyFilter($query, $filter);
        }

        if ($returnIds) {
            $deletedRecordsIds = $query->pluck('_id');
        }

        return $returnIds ? $deletedRecordsIds->all() : $query->delete();
    }

    protected function query()
    {
        $collection = 'store-' . strtolower($this->model->getPluralName());
        return DB::collection($collection);
    }

    protected function applyFilter($query, $filter, $or = false)
    {
        if (isset($filter['AND'])) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter['AND'] as $subFilter) {
                    $this->applyFilter($query, $subFilter);
                }
            });
        } else if (isset($filter['OR'])) {
            $query->where(function ($query) use ($filter) {
                foreach ($filter['OR'] as $subFilter) {
                    $this->applyFilter($query, $subFilter, true);
                }
            });
        } else if ($or) {
            $query->orWhere(
                $this->getDatabaseFieldName($filter['field']),
                isset($filter['operation']) ? $filter['operation'] : '=',
                $this->prepareDatabaseValue($filter['field'], $filter['value'])
            );
        } else {
            $query->where(
                $this->getDatabaseFieldName($filter['field']),
                isset($filter['operation']) ? $filter['operation'] : '=',
                $this->prepareDatabaseValue($filter['field'], $filter['value'])
            );
        }
    }

    protected function applyOrderBy($query, $orderBy)
    {
        if (isset($orderBy['AND'])) {
            foreach ($orderBy['AND'] as $subOrderBy) {
                $this->applyOrderBy($query, $subOrderBy);
            }
        } else {
            $query->orderBy(
                $this->getDatabaseFieldName($orderBy['field']),
                isset($orderBy['direction']) ? $orderBy['direction'] : 'asc'
            );
        }
    }

    protected function getDatabaseFieldName($field)
    {
        return $field === $this->model->getPrimaryKeyName()
            ? '_id'
            : $field;
    }

    protected function prepareDatabaseValues($values)
    {
        foreach ($this->model->getFieldNames(Type::ID) as $field) {
            if (isset($values[$field])) {
                $values[$field] = MongoDB::key($values[$field], true);
            }
        }

        foreach ($this->model->getFieldNames(DateType::NAME) as $field) {
            if (isset($values[$field])) {
                $values[$field] = MongoDB::date($values[$field], true);
            }
        }

        return $values;
    }

    protected function prepareDatabaseValue($field, $value)
    {
        switch ($this->model->getFieldTypeName($field)) {
            case Type::ID:
                return MongoDB::key($value);
            case DateType::NAME:
                return MongoDB::date($value);
            default:
                return $value;
        }
    }

    protected function convertResult($document)
    {
        $document = MongoDB::convertDocument($document);

        if (isset($document['_id'])) {
            $document[$this->model->getPrimaryKeyName()] = $document['_id'];
            unset($document['_id']);
        }

        return $document;
    }
}
