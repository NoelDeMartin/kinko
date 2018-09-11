<?php

namespace Kinko\Models;

use Kinko\Database\MongoDB\Soukai\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'description', 'logo_url', 'homepage_url',
        'user_id', 'secret', 'redirect_uris',
        'validated', 'revoked',
        'schema',
    ];

    protected $casts = [
        'schema' => 'document',
    ];
}
