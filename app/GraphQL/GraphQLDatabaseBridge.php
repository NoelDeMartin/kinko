<?php

namespace Kinko\GraphQL;

interface GraphQLDatabaseBridge
{
    /**
     * Create new query for given type.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @param \GraphQL\Type\Definition\Type $type
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(SchemaModel $model);

    /**
     * Prepare values to be inserted in the database.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @param mixed $values
     * @return mixed
     */
    public function prepareValues(SchemaModel $model, $values);

    /**
     * Convert database result into supported GraphQL Schema types.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @param mixed $result
     * @return mixed
     */
    public function convertResult(SchemaModel $model, $result);
}
