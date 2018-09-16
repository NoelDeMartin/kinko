<?php

namespace Kinko\Database\MongoDB\Soukai;

use InvalidArgumentException;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

use Kinko\Support\Facades\MongoDB;
use Kinko\Database\Soukai\NonRelationalModel;
use Kinko\Database\MongoDB\Soukai\Concerns\HasKeys;
use Kinko\Database\MongoDB\Soukai\Relations\HasOne;
use Kinko\Database\MongoDB\Soukai\Relations\BelongsTo;
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

    public function getForeignKey()
    {
        return Str::snake(class_basename($this)).'_id';
    }

    public function getKey()
    {
        return MongoDB::key(parent::getKey());
    }

    public function getCasts()
    {
        $keyCasts = [];

        foreach ($this->keys as $key) {
            $keyCasts[$key] = 'string';
        }

        return array_merge($keyCasts, parent::getCasts());
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        if (is_null($foreignKey)) {
            $foreignKey = $this->newRelatedInstance($related)->getForeignKey();
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    protected function newHasOne(
        EloquentBuilder $query,
        EloquentModel $parent,
        $foreignKey,
        $localKey
    )
    {
        if (!$query instanceof Builder) {
            throw new InvalidArgumentException('hasOne query must be instance of ' . Builder::class);
        }
        if (!$parent instanceof Model) {
            throw new InvalidArgumentException('hasOne model must be instance of ' . Model::class);
        }
        return new HasOne($query, $parent, $foreignKey, $localKey);
    }

    protected function newBelongsTo(
        EloquentBuilder $query,
        EloquentModel $child,
        $foreignKey,
        $ownerKey,
        $relation
    )
    {
        if (!$query instanceof Builder) {
            throw new InvalidArgumentException('belongsTo query must be instance of ' . Builder::class);
        }
        if (!$child instanceof Model) {
            throw new InvalidArgumentException('belongsTo model must be instance of ' . Model::class);
        }
        return new BelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'document':
                return MongoDB::convertDocument($value);
            default:
                return parent::castAttribute($key, $value);
        }
    }
}
