<?php

namespace Kinko\Database\Soukai;

use Illuminate\Database\Eloquent\Model;
use Kinko\Database\Query\NonRelationalBuilder as QueryBuilder;

class NonRelationalModel extends Model
{
    protected $keyType = 'string';

    protected $resource = null;

    public function getResourceClass()
    {
        return is_null($this->resource)
            ? app()->getNamespace() . 'Http\\Resources\\' . class_basename($this)
            : $this->resource;
    }

    public function resource()
    {
        $resourceClass = $this->getResourceClass();
        return new $resourceClass($this);
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
}
