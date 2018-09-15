<?php

namespace Kinko\Models;

use Kinko\Database\MongoDB\Soukai\Model;

class AccessToken extends Model
{
    protected $fillable = [
        'id', 'user_id', 'client_id',
        'scopes', 'revoked', 'expires_at',
    ];

    protected $dates = [
        'expires_at', 'created_at', 'updated_at',
    ];

    protected $keys = [
        'user_id', 'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
