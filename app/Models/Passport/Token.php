<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class Token extends Model
{
    protected $collection = 'oauth_tokens';
}
