<?php

namespace Kinko\Database\MongoDB\Query;

use Illuminate\Support\Collection;
use Kinko\Database\Query\NonRelationalBuilder;

class Builder extends NonRelationalBuilder
{
    protected $orders = [];

    public function __construct($connection, $collection)
    {
        parent::__construct($connection, $collection);
    }

    public function orderBy($field, $direction = 'asc')
    {
        if (is_string($direction)) {
            $direction = (strtolower($direction) == 'asc' ? 1 : -1);
        }

        $this->orders[$field] = $direction;

        return $this;
    }

    public function pluck($field, $key = null)
    {
        $results = $this->aggregate($this->buildPipeline());

        return (new Collection($results))->pluck($field, $key);
    }

    public function accumulate($field, $operation)
    {
        $results = $this->aggregate([
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

    public function insert(array $values)
    {
        $documents = $this->prepareDocuments($values);
        $result = $this->getCollection()->insertMany($documents);

        return $result->getInsertedCount() === count($documents);
    }

    protected function getCollection()
    {
        return $this->connection->getDatabase()->selectCollection($this->collection);
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

        if (!empty($this->orders)) {
            $pipeline[] = [ '$sort' => $this->orders ];
        }

        return $pipeline;
    }

    // TODO this may cause issues with laravel's aggregate
    protected function aggregate($pipeline)
    {
        return $this->getCollection()->aggregate($pipeline, [ 'useCursor' => false ]);
    }
}
