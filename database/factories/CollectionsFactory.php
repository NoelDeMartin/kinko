<?php

use Kinko\Models\Collection;
use Faker\Generator as Faker;

$factory->define(Collection::class, function (Faker $faker) {
    return [
        'name' => $faker->unique->word,
    ];
});
