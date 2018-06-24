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

    protected $hidden = [
        'secret',
    ];

    public function authCodes()
    {
        return $this->hasMany(AuthCode::class, 'client_id');
    }

    public function tokens()
    {
        return $this->hasMany(AccessToken::class, 'client_id');
    }

    public function firstParty()
    {
        return $this->personal_access_client || $this->password_client;
    }
}
