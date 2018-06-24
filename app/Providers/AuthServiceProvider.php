<?php

namespace Kinko\Providers;

use Laravel\Passport\Passport;
use Kinko\Models\Passport\Client;
use Kinko\Auth\MongoUserProvider;
use Kinko\Models\Passport\AuthCode;
use Illuminate\Support\Facades\Auth;
use Kinko\Models\Passport\AccessToken;
use Kinko\Auth\Passport\ClientRepository;
use Kinko\Auth\Passport\AuthCodeRepository;
use Kinko\Models\Passport\PersonalAccessClient;
use Kinko\Auth\Passport\RefreshTokenRepository;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Laravel\Passport\Bridge\AuthCodeRepository as PassportAuthCodeRepository;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Bridge\RefreshTokenRepository as PassportRefreshTokenRepository;

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
    }

    /**
     * Register passport models.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
        Passport::useTokenModel(AccessToken::class);
        Passport::useClientModel(Client::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);

        $this->app->bind(PassportClientRepository::class, ClientRepository::class);
        $this->app->bind(PassportAuthCodeRepository::class, AuthCodeRepository::class);
        $this->app->bind(PassportRefreshTokenRepository::class, RefreshTokenRepository::class);
    }
}
