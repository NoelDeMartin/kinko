<?php

namespace Kinko\Database\Soukai;

use Kinko\Database\Query\NonRelationalBuilder as QueryBuilder;

class NonRelationalBuilder
{
    protected $query;

    protected $model = null;

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    public function setModel(NonRelationalModel $model)
    {
        $this->model = $model;

        return $this;
    }

    public function where($field, $value)
    {
        $this->query->where($field, $value);

        return $this;
    }

    public function create(array $attributes = [])
    {
        return tap($this->model->newInstance($attributes))->save();
    }

    public function update(array $values)
    {
        return $this->query->update($values);
    }

    public function insertAndGetKey(array $values, $key = null)
    {
        return $this->query->insertAndGetKey($values, $key);
    }

    public function first()
    {
        $document = $this->query->first();

        return is_null($document)? null : $this->model->newFromBuilder($document, true);
    }
}
