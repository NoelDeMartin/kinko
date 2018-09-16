<?php

namespace Kinko\Database\MongoDB\Soukai\Relations;

use Kinko\Support\Facades\MongoDB;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BaseBelongsTo;

class BelongsTo extends BaseBelongsTo
{
    public function getEagerModelKeys(array $models)
    {
        $keys = parent::getEagerModelKeys($models);

        foreach ($keys as $i => $key) {
            $keys[$i] = MongoDB::key($key);
        }

        return $keys;
    }
}
