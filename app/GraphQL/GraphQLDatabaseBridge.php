<?php

namespace Kinko\GraphQL;

interface GraphQLDatabaseBridge
{
    /**
     * Create new model.
     *
     * @param mixed $args
     * @return mixed
     */
    public function create($args);

    /**
     * Retrieve models.
     *
     * @param array $restrictions
     * @return array
     */
    public function retrieve(array $restrictions);

    /**
     * Update model.
     *
     * @param mixed $id
     * @param mixed $args
     * @return mixed
     */
    public function update($id, $args);

    /**
     * Delete model.
     *
     * @param mixed $id
     */
    public function delete($id);
}
