<?php

namespace Kinko\Models;

use Illuminate\Auth\Authenticatable;
use Kinko\Database\MongoDB\Soukai\Model;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract, UserEntityInterface
{
    static function newApiToken()
    {
        do {
            $token = str_random();
        } while (static::where('api_token', $token)->count() > 0);

        return $token;
    }

    use Authenticatable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'api_token',
    ];

    protected $hidden = [
        'password', 'api_token',
    ];

    public function getIdentifier()
    {
        return $this->id;
    }
}
