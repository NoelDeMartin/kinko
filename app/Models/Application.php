<?php

namespace Kinko\Models;

use Kinko\Models\Passport\Client;
use Kinko\Database\MongoDB\Soukai\Model;

class Application extends Model
{
    protected $fillable = [
        'name', 'description', 'domain', 'logo_url', 'homepage_url',
        'schema', 'client_id', 'validated',

        // TODO remove legacy fields
        'callback_url', 'redirect_url',
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
