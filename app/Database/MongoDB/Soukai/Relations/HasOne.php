<?php

namespace Kinko\Database\MongoDB\Soukai\Relations;

use Kinko\Support\Facades\MongoDB;
use Illuminate\Database\Eloquent\Relations\HasOne as BaseHasOne;

class HasOne extends BaseHasOne
{
    public function getParentKey()
    {
        return MongoDB::key($this->parent->getAttribute($this->localKey));
    }
}
