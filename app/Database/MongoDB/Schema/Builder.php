<?php

namespace Kinko\Database\MongoDB\Schema;

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

    protected function createBlueprint($collection)
    {
        return new Blueprint($collection);
    }
}
