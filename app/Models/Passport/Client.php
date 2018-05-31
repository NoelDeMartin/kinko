<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class Client extends Model
{
    protected $collection = 'oauth_clients';
}
