<?php

namespace Kinko\Database\MongoDB\Soukai;

use Kinko\Database\Soukai\NonRelationalModel;

class Model extends NonRelationalModel
{
    protected $primaryKey = '_id';

    protected $keyType = 'objectid';

    protected $casts = [
        'id' => 'objectid',
        '_id' => 'objectid',
    ];

    public function getIdAttribute()
    {
        return isset($this->attributes['_id']) ? $this->attributes['_id'] : null;
    }
}
