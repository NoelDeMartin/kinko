<?php

namespace Kinko\Database\Soukai;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

class NonRelationalModel
{
    use HasAttributes;
    use HasTimestamps;
    use HasRelationships;
    use GuardsAttributes;

    protected static $resolver;

    public $exists = false;

    public $incrementing = true;

    // TODO move this to mongo-specific
    protected $primaryKey = '_id';
    protected $keyType = 'objectid';

    protected $resource = null;

    protected $connection = null;

    protected $collection = null;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    public function __construct(array $attributes = [], $exists = false)
    {
        $this->syncOriginal();
        $this->fill($attributes);
        $this->exists = $exists;
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key,
                    get_class($this)
                ));
            }
        }

        return $this;
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $query = $this->newQuery();

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
            $saved = $this->isDirty() ? $this->performUpdate($query) : true;
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert($query);
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->syncOriginal();
        }

        return $saved;
    }

    /**
     * Perform a model update operation.
     *
     * @param  \Kinko\Database\Soukai\NonRelationalBuilder  $query
     * @return bool
     */
    protected function performUpdate(NonRelationalBuilder $query)
    {
        // First we need to create a fresh query instance and touch the creation and
        // update timestamp on the model which are maintained by us for developer
        // convenience. Then we will just continue saving the model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $this->setKeysForSaveQuery($query)->update($dirty);

            $this->syncChanges();
        }

        return true;
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Kinko\Database\Soukai\NonRelationalBuilder  $query
     * @return bool
     */
    protected function performInsert(NonRelationalBuilder $query)
    {
        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->attributes;

        if (empty($attributes)) {
            return true;
        }

        $key = $query->insertAndGetKey($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $key);

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        return true;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  \Kinko\Database\Soukai\NonRelationalBuilder  $query
     * @return \Kinko\Database\Soukai\NonRelationalBuilder
     */
    protected function setKeysForSaveQuery(NonRelationalBuilder $query)
    {
        $query->where($this->getKeyName(), $this->getKeyForSaveQuery());

        return $query;
    }

    public function getResourceClass()
    {
        return is_null($this->resource)
            ? app()->getNamespace() . 'Http\\Resources\\' . class_basename($this)
            : $this->resource;
    }

    public function resource()
    {
        $class = $this->getResourceClass();
        return new $class($this);
    }

    /**
     * Get the collection associated with the model.
     *
     * @return string
     */
    public function getCollection()
    {
        if (is_null($this->collection)) {
            return str_replace(
                '\\',
                '',
                Str::snake(Str::plural(class_basename($this)))
            );
        }

        return $this->collection;
    }

    /**
     * Get the value indicating whether the keys are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return $this->keyType;
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery()
    {
        return $this->original[$this->getKeyName()]
            ?? $this->getKey();
    }

    /**
     * Get a new query builder for the model's collection.
     *
     * @return \Kinko\Database\Soukai\NonRelationalBuilder
     */
    public function newQuery()
    {
        return (new NonRelationalBuilder($this->getConnection()->collection($this->getCollection())))->setModel($this);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance(array $attributes = [], $exists = false)
    {
        return new static($attributes, $exists);
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @return static
     */
    public function newFromBuilder(array $attributes = [])
    {
        $model = $this->newInstance([], true);

        $model->setRawAttributes($attributes, true);

        return $model;
    }

    /**
     * Get the database connection for the model.
     *
     * @return \Kinko\Database\NonRelationalConnection
     */
    public function getConnection()
    {
        return static::resolveConnection($this->getConnectionName());
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Resolve a connection instance.
     *
     * @param  string|null  $connection
     * @return \Kinko\Database\NonRelationalConnection
     */
    public static function resolveConnection($connection = null)
    {
        return static::$resolver->connection($connection);
    }

    /**
     * Get the connection resolver instance.
     *
     * @return Illuminate\Database\ConnectionResolverInterface
     */
    public static function getConnectionResolver()
    {
        return static::$resolver;
    }

    // TODO move this to mongo-specific
    public function getIdAttribute()
    {
        return isset($this->attributes['_id'])? $this->attributes['_id'] : null;
    }

    /**
     * Set the connection resolver instance.
     *
     * @param  Illuminate\Database\ConnectionResolverInterface  $resolver
     * @return void
     */
    public static function setConnectionResolver(Resolver $resolver)
    {
        static::$resolver = $resolver;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }
}
