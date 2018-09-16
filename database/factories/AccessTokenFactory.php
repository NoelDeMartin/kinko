<?php

use Kinko\Models\AccessToken;
use Faker\Generator as Faker;

$factory->define(AccessToken::class, function (Faker $faker) {
    return [
        'id' => str_random(),
        'scopes' => [],
        'revoked' => false,
        'expires_at' => now()->addMonth(),
    ];
});
