<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id', 'name', 'secret', 'redirect',
        'personal_access_client', 'password_client',
        'revoked',
    ];
}
