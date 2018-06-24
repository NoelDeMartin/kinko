<?php

namespace Kinko\Database\MongoDB\Soukai\Concerns;

use Kinko\Support\Facades\MongoDB;

trait HasKeys
{
    protected $keys = [];

    public function fromKey($value)
    {
        return MongoDB::key($value);
    }

    protected function isKeyAttribute($key)
    {
        return in_array($key, $this->getKeys());
    }

    public function getKeys()
    {
        $defaults = [$this->getKeyName()];

        return array_unique(array_merge($this->keys, $defaults));
    }
}
