<?php

namespace Kinko\Models;

use Illuminate\Auth\Authenticatable;
use Kinko\Database\Soukai\NonRelationalModel;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends NonRelationalModel implements AuthenticatableContract
{
    use Authenticatable;

    protected $fillable = [ 'first_name', 'last_name', 'email', 'password' ];

    protected $hidden = [ 'password' ];
}
