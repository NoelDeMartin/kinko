<?php

use Kinko\Models\Client;
use Faker\Generator as Faker;

use Kinko\Models\Passport\Client as LegacyClient;

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

$factory->define(LegacyClient::class, function (Faker $faker) {
    return [
        'name' => $faker->unique->word,
        'secret' => str_random(40),
        'redirect' => $faker->url,
        'personal_access_client' => false,
        'password_client' => false,
        'revoked' => false,
    ];
});
