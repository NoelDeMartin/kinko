<?php

namespace Kinko\Database\MongoDB\Soukai;

use Kinko\Database\MongoDB\Query\Builder;
use Kinko\Database\Soukai\NonRelationalModel;
use Kinko\Database\Soukai\NonRelationalBuilder;

class Model extends NonRelationalModel
{
    protected $primaryKey = '_id';

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new NonRelationalBuilder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new Builder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }

    public function getIdAttribute()
    {
        return isset($this->attributes['_id']) ? (string) $this->attributes['_id'] : null;
    }
}
