<?php

namespace Kinko\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Kinko\Database\MongoDB\Soukai\Model;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Model implements AuthenticatableContract, UserEntityInterface
{
    use HasApiTokens;
    use Authenticatable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function getIdentifier()
    {
        return $this->id;
    }
}
