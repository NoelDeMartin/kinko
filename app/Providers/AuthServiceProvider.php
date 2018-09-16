<?php

namespace Kinko\Providers;

use DateInterval;
use Laravel\Passport\Passport;
use Kinko\Auth\MongoUserProvider;
use League\OAuth2\Server\CryptKey;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Server\ResourceServer;
use Kinko\Auth\OAuth\Grants\AuthCodeGrant;
use League\OAuth2\Server\AuthorizationServer;
use Kinko\Auth\OAuth\Repositories\ScopeRepository;
use Kinko\Auth\OAuth\Repositories\ClientRepository;
use Kinko\Auth\OAuth\Repositories\AuthCodeRepository;
use Kinko\Auth\OAuth\Repositories\AccessTokenRepository;
use Kinko\Auth\OAuth\Repositories\RefreshTokenRepository;
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
    }

    /**
     * Register passport models.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();

        $this->app->singleton(AuthorizationServer::class, function () {
            $server = new AuthorizationServer(
                new ClientRepository,
                new AccessTokenRepository,
                new ScopeRepository,
                new CryptKey('file://' . storage_path('oauth-private.key'), null, false),
                $this->app['encrypter']->getKey()
            );

            $authCodeGrant = new AuthCodeGrant(
                new AuthCodeRepository,
                new RefreshTokenRepository,
                new DateInterval('PT10M')
            );

            $server->enableGrantType($authCodeGrant, new DateInterval('P1Y'));

            return $server;
        });

        $this->app->singleton(ResourceServer::class, function () {
            return new ResourceServer(
                new AccessTokenRepository,
                new CryptKey('file://' . storage_path('oauth-public.key'), null, false)
            );
        });
    }
}
