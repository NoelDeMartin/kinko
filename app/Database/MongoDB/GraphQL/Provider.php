<?php

namespace Kinko\Database\MongoDB\GraphQL;

use Kinko\GraphQL\SchemaModel;
use Kinko\GraphQL\GraphQLDatabaseProvider;

class Provider implements GraphQLDatabaseProvider
{
    public function bridge(SchemaModel $model)
    {
        return new Bridge($model);
    }
}