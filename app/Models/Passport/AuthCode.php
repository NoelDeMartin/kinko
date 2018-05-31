<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class AuthCode extends Model
{
    protected $collection = 'oauth_auth_codes';
}
