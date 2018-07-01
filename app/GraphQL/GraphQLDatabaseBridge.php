<?php

namespace Kinko\GraphQL;

interface GraphQLDatabaseBridge
{
    /**
     * Create new query for given type.
     *
     * @param \GraphQL\Type\Definition\Type $type
     * @return \Illuminate\Database\Query\Builder
     */
    public function query($type);

    /**
     * Convert database result into supported GraphQL type.
     *
     * @param mixed $result
     * @return mixed
     */
    public function convertResult($result);
}
