<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class PersonalAccessClient extends Model
{
    protected $fillable = [
        'client_id',
    ];

    protected $keys = [
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
