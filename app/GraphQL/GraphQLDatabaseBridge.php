<?php

namespace Kinko\GraphQL;

interface GraphQLDatabaseBridge
{
    /**
     * Create new model.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @param mixed $args
     * @return mixed
     */
    public function create(SchemaModel $model, $args);

    /**
     * Retrieve models.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @return array
     */
    public function retrieve(SchemaModel $model);

    /**
     * Update model.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @param mixed $id
     * @param mixed $args
     * @return mixed
     */
    public function update(SchemaModel $model, $id, $args);

    /**
     * Delete model.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @param mixed $id
     */
    public function delete(SchemaModel $model, $id);
}
