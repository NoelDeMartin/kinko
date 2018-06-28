<?php

namespace Kinko\Database\MongoDB\Soukai;

use Kinko\Support\Facades\MongoDB;
use Kinko\Database\Soukai\NonRelationalModel;
use Kinko\Database\MongoDB\Soukai\Concerns\HasKeys;
use Kinko\Database\MongoDB\Query\Builder as QueryBuilder;
use Kinko\Database\MongoDB\Soukai\Builder as SoukaiBuilder;

class Model extends NonRelationalModel
{
    use HasKeys;

    protected $primaryKey = '_id';

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new SoukaiBuilder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor()
        );
    }

    public function fromDateTime($value)
    {
        return MongoDB::date($value);
    }

    protected function asDateTime($value)
    {
        return parent::asDateTime(MongoDB::convertDate($value));
    }

    public function setAttribute($key, $value)
    {
        if (!is_null($value) && $this->isKeyAttribute($key)) {
            $value = $this->fromKey($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getIdAttribute()
    {
        return isset($this->attributes['id'])
            ? $this->attributes['id']
            : (isset($this->attributes['_id'])
                ? (string) $this->attributes['_id']
                : null);
    }

    public function getCasts()
    {
        $keyCasts = [];

        foreach ($this->keys as $key) {
            $keyCasts[$key] = 'string';
        }

        return array_merge($keyCasts, parent::getCasts());
    }
}
