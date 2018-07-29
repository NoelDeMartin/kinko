<?php

namespace Kinko\GraphQL;

interface GraphQLDatabaseBridge
{
    /**
     * Create new model.
     *
     * @param array $args
     * @return mixed
     */
    public function create(array $args);

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
     * @param array $filter
     * @param array $args
     * @param bool $returnObjects
     * @return mixed
     */
    public function update(array $filter, array $args, bool $returnObjects);

    /**
     * Delete model.
     *
     * @param mixed $id
     */
    public function delete($id);
}
