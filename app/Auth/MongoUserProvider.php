<?php

namespace Kinko\Auth;

use MongoDB\BSON\ObjectId;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Auth\EloquentUserProvider;

class MongoUserProvider extends EloquentUserProvider
{
    public function __construct(Hasher $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    public function retrieveById($identifier)
    {
        return parent::retrieveById(new ObjectId($identifier));
    }
}
