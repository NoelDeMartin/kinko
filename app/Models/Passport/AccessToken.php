<?php

namespace Kinko\Models\Passport;

use Kinko\Database\MongoDB\Soukai\Model;

class AccessToken extends Model
{
    protected $fillable = [
        'id', 'user_id', 'client_id',
        'scopes', 'revoked', 'expires_at',
        'created_at', 'updated_at',
    ];

    protected $dates = [
        'expires_at', 'created_at', 'updated_at',
    ];

    protected $keys = [
        'user_id', 'client_id',
    ];

    public $timestamps = false;

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        $provider = config('auth.guards.api.provider');

        return $this->belongsTo(config('auth.providers.' . $provider . '.model'));
    }

    public function can($scope)
    {
        return in_array('*', $this->scopes) ||
            array_key_exists($scope, array_flip($this->scopes));
    }

    public function cant($scope)
    {
        return !$this->can($scope);
    }

    public function revoke()
    {
        return $this->forceFill(['revoked' => true])->save();
    }

    public function transient()
    {
        return false;
    }
}
