<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class PersonalAccessClient extends Model
{
    protected $collection = 'oauth_personal_access_clients';
}
