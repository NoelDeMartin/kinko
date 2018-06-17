<?php

use Kinko\Models\Application;
use Faker\Generator as Faker;

$factory->define(Application::class, function (Faker $faker) {
    $domain = $faker->unique->domainName;

    return [
        'name' => $faker->unique->word,
        'description' => $faker->sentence,
        'domain' => $domain,
        'callback_url' => 'http://' . $domain . '/' . $faker->word,
        'redirect_url' => 'http://' . $domain . '/' . $faker->word,
        'schema' => [],
    ];
});
