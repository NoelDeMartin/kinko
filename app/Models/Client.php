<?php

namespace Kinko\Models;

use Kinko\Database\MongoDB\Soukai\Model;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class Client extends Model implements ClientEntityInterface
{
    protected $fillable = [
        'name', 'description', 'logo_url', 'homepage_url',
        'user_id', 'secret', 'redirect_uris',
        'validated', 'revoked',
        'schema',
    ];

    protected $casts = [
        'schema' => 'document',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $keys = [
        'user_id',
    ];

    public function tokens()
    {
        return $this->hasMany(AccessToken::class);
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uris[0];
    }
}
