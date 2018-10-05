<?php

namespace Kinko\Models;

use Kinko\Database\MongoDB\Soukai\Model;

class Collection extends Model
{
    protected $fillable = ['name', 'type'];

    protected $casts = [
        'type' => 'document',
    ];
}
