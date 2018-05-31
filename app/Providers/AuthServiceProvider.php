<?php

namespace Kinko\Providers;

use Laravel\Passport\Passport;
use Kinko\Models\Passport\Token;
use Kinko\Models\Passport\Client;
use Kinko\Auth\MongoUserProvider;
use Kinko\Models\Passport\AuthCode;
use Illuminate\Support\Facades\Auth;
use Kinko\Models\Passport\PersonalAccessClient;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('mongodb', function ($app, array $config) {
            return new MongoUserProvider($app->make('hash'), $config['model']);
        });
        Passport::routes();
    }

    /**
     * Register passport models.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
    }
}
