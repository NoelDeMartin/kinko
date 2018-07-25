<?php

namespace Kinko\GraphQL;

interface GraphQLDatabaseProvider {
    /**
     * Get bridge for model.
     *
     * @param \Kinko\GraphQL\SchemaModel $model
     * @return \Kinko\GraphQL\GraphQLDatabaseBridge
     */
    public function bridge(SchemaModel $model);
}
