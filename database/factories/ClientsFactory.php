<?php

use Faker\Generator as Faker;
use Kinko\Models\Passport\Client;

$factory->define(Client::class, function (Faker $faker) {
    return [
        'name' => $faker->unique->word,
        'secret' => str_random(40),
        'redirect' => $faker->url,
        'personal_access_client' => false,
        'password_client' => false,
        'revoked' => false,
    ];
});
