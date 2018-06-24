<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class AuthCode extends Model
{
    protected $fillable = [
        'id', 'user_id', 'client_id',
        'scopes', 'revoked', 'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
