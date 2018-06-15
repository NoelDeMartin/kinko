<?php

namespace Kinko\Database\MongoDB\Schema;

use Closure;
use Kinko\Database\Schema\NonRelationalBuilder;

class Builder extends NonRelationalBuilder
{
    public function __construct($connection)
    {
        parent::__construct($connection);
    }

    public function hasCollection($collection)
    {
        $db = $this->connection->getDatabase();

        foreach ($db->listCollections() as $existingCollection) {
            if ($existingCollection->getName() === $collection) {
                return true;
            }
        }

        return false;
    }

    public function dropAllCollections()
    {
        $db = $this->connection->getDatabase();

        foreach ($db->listCollections() as $collection) {
            $db->dropCollection($collection->getName());
        }
    }

    protected function createBlueprint($collection, Closure $closure = null)
    {
        if ($closure !== null) {
            // TODO implement
            throw new InvalidArgumentException('Operation not implemented for MongoDB.');
        }

        return new Blueprint($collection);
    }
}
