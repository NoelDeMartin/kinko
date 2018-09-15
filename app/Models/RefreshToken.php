<?php

namespace Kinko\Models;

use Kinko\Database\MongoDB\Soukai\Model;

class RefreshToken extends Model
{
    protected $fillable = [
        'id', 'access_token_id', 'revoked', 'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    public $timestamps = false;
}
