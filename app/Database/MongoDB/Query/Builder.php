<?php

namespace Kinko\Database\MongoDB\Query;

use InvalidArgumentException;
use Illuminate\Support\Collection;
use Kinko\Support\Facades\MongoDB;
use Kinko\Database\Query\NonRelationalBuilder;

class Builder extends NonRelationalBuilder
{
    public function where($field, $operator = null, $value = null, $boolean = 'and')
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        if ($field instanceof Closure) {
            return $this->whereNested($field, $boolean);
        }

        if (!in_array($operator, ['=', '>', '>=', '<', '<='])) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        if ($boolean !== 'and') {
            throw new InvalidArgumentException('Illegal boolean operation.');
        }

        if (starts_with($field, $this->from . '.')) {
            $field = substr($field, strlen($this->from . '.'));
        }

        $type = 'Basic';

        $this->wheres[] = compact('type', 'field', 'operator', 'value', 'boolean');

        return $this;
    }

    public function whereIn($field, $values, $boolean = 'and', $not = false)
    {
        if ($boolean !== 'and') {
            throw new InvalidArgumentException('Illegal boolean operation.');
        }

        if ($not !== false) {
            // TODO implement
            throw new InvalidArgumentException('Operation not implemented for MongoDB.');
        }

        $type = $not ? 'NotIn' : 'In';

        $this->wheres[] = compact('type', 'field', 'values', 'boolean');

        return $this;
    }

    public function orderBy($field, $direction = 'asc')
    {
        if (is_string($direction)) {
            $direction = (strtolower($direction) == 'asc' ? 1 : -1);
        }

        $this->orders[$field] = $direction;

        return $this;
    }

    public function first($fields = ['*'])
    {
        if (count($fields) > 1 || (count($fields) === 1 && $fields[0] !== '*')) {
            // TODO implement
            throw new InvalidArgumentException('Operation not implemented for MongoDB.');
        }

        $this->limit = 1;

        $results = $this->mongoAggregate($this->buildPipeline());

        return count($results) > 0 ? (array) $results[0] : null;
    }

    public function get($fields = ['*'])
    {
        if (count($fields) > 1 || (count($fields) === 1 && $fields[0] !== '*')) {
            // TODO implement
            throw new InvalidArgumentException('Operation not implemented for MongoDB.');
        }

        $results = $this->mongoAggregate($this->buildPipeline());

        return new Collection(array_map(function ($document) {
            return (array) $document;
        }, $results));
    }

    public function pluck($field, $key = null)
    {
        $results = $this->mongoAggregate($this->buildPipeline());

        return (new Collection($results))->pluck($field, $key);
    }

    public function accumulate($field, $operation)
    {
        $results = $this->mongoAggregate([
            [
                '$group' => [
                    '_id' => null,
                    'accumulation' => [
                        ('$' . $operation) => ('$' . $field)
                    ]
                ]
            ]
        ]);

        return count($results) > 0? $results[0]['accumulation'] : null;
    }

    public function count($fields = [])
    {
        if (!empty($fields)) {
            // TODO implement
            throw new InvalidArgumentException('Operation not implemented for MongoDB.');
        }

        $pipeline = $this->buildPipeline();
        $pipeline[] = ['$count' => 'count'];
        $results = $this->mongoAggregate($pipeline);

        return count($results) > 0 ? $results[0]['count'] : 0;
    }

    public function update(array $updated, array $removed = [])
    {
        $operations = [
            '$set' => $updated,
        ];

        if (!empty($removed)) {
            $operations['$unset'] = [];

            foreach ($removed as $field) {
                $operations['$unset'][$field] = true;
            }
        }

        $result = $this->getCollection()->updateMany(
            $this->buildWheresMatch(),
            $operations
        );

        return $result->getModifiedCount();
    }

    public function insert(array $values)
    {
        $documents = $this->prepareDocuments($values);
        $result = $this->getCollection()->insertMany($documents);

        return $result->getInsertedCount() === count($documents);
    }

    public function insertGetId(array $values, $key = null)
    {
        if (!is_null($key) && $key !== '_id') {
            throw new InvalidArgumentException("Primary key field should be '_id', '{$key}' given");
        }

        $result = $this->getCollection()->insertOne($values);

        if ($result->getInsertedCount() === 1) {
            return $result->getInsertedId();
        }
    }

    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->where('_id', $id);
        }

        $result = $this->getCollection()->deleteMany($this->buildWheresMatch());

        return $result->getDeletedCount();
    }

    protected function getCollection()
    {
        return $this->connection->getDatabase()->selectCollection($this->from);
    }

    protected function prepareDocuments($values)
    {
        foreach ($values as $value) {
            if (!is_array($value)) {
                return [$values];
            }
        }

        return $values;
    }

    protected function buildPipeline()
    {
        $pipeline = [];

        if (!empty($this->wheres)) {
            $pipeline[] = [ '$match' => $this->buildWheresMatch() ];
        }

        if (!empty($this->orders)) {
            $pipeline[] = [ '$sort' => $this->orders ];
        }

        if (!is_null($this->limit)) {
            $pipeline[] = [ '$limit' => $this->limit ];
        }

        return $pipeline;
    }

    protected function buildWheresMatch()
    {
        $match = [];
        foreach ($this->wheres as $where) {
            switch ($where['type']) {
                case 'Basic':
                    switch ($where['operator']) {
                        case '=':
                            $match[$where['field']] = $where['value'];
                            break;
                        case '>':
                            $match[$where['field']] = ['$gt' => $where['value']];
                            break;
                        case '>=':
                            $match[$where['field']] = ['$gte' => $where['value']];
                            break;
                        case '<':
                            $match[$where['field']] = ['$lt' => $where['value']];
                            break;
                        case '<=':
                            $match[$where['field']] = ['$lte' => $where['value']];
                            break;
                    }
                    break;
                case 'In':
                    $match[$where['field']] = [
                        '$in' => $where['values'],
                    ];
                    break;
                case 'NotIn':
                    // TODO
                    break;
            }
        }

        return $match;
    }

    protected function mongoAggregate($pipeline)
    {
        return $this->getCollection()->aggregate($pipeline)->toArray();
    }
}
