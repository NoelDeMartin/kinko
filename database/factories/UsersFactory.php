<?php

use Kinko\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'first_name' => $faker->firstName(),
        'last_name'  => $faker->lastName(),
        'email'      => $faker->email,
        'password'   => bcrypt('secret'),
        'api_token'  => str_random(),
    ];
});
