<?php

namespace Kinko\Database\MongoDB\GraphQL;

use Illuminate\Support\Facades\DB;
use Kinko\Support\Facades\MongoDB;
use Kinko\GraphQL\GraphQLDatabaseBridge;

class Bridge implements GraphQLDatabaseBridge
{
    public function query($type)
    {
        $collection = 'store-' . strtolower(str_plural($type->name));
        return DB::collection($collection);
    }

    public function convertResult($document)
    {
        $document = MongoDB::convertDocument($document);

        if (isset($document['_id'])) {
            $document['id'] = $document['_id'];
            unset($document['_id']);
        }

        return $document;
    }
}
