<?php

namespace Kinko\Database\MongoDB\Schema;

use Kinko\Database\Schema\NonRelationalBlueprint;

class Blueprint extends NonRelationalBlueprint
{
    public function __construct($collection)
    {
        parent::__construct($collection);
    }

    protected function createCollection()
    {
        $this->connection->getDatabase()->createCollection($this->collection);
    }

    protected function createIndex($field, $index, $parameters)
    {
        $collection = $this->connection->getDatabase()->selectCollection($this->collection);
        $collection->createIndex([ $field => 1 ], [ $index => $parameters ]);
    }
}
