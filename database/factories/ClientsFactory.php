<?php

use Kinko\Models\Client;
use Faker\Generator as Faker;

$factory->define(Client::class, function (Faker $faker) {
    $domain = $faker->unique->domainName;

    return [
        'name' => $faker->unique->company,
        'description' => $faker->unique->sentence,
        'logo_url' => $faker->unique->imageUrl,
        'homepage_url' => 'https://' . $domain,
        'redirect_uris' => [
            'https://' . $domain . '/' . $faker->unique->word,
        ],
    ];
});
