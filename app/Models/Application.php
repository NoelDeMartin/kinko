<?php

namespace Kinko\Models;

use Kinko\Models\Passport\Client;
use Kinko\Database\MongoDB\Soukai\Model;

class Application extends Model
{
    protected $fillable = [
        'name', 'description', 'domain', 'callback_url', 'redirect_url',
        'schema', 'client_id',
    ];

    protected $casts = [
        'schema' => 'document',
    ];

    protected $keys = [
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
