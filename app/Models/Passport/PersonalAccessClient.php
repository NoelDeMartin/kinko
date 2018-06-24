<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class PersonalAccessClient extends Model
{
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
