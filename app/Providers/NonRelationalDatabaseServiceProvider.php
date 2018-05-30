<?php

namespace Kinko\Providers;

use Kinko\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Kinko\Database\Soukai\NonRelationalModel;
use Kinko\Database\MongoDB\Connection as MongoDBConnection;

class NonRelationalDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        NonRelationalModel::setConnectionResolver($this->app['db']);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app);
        });
        $this->app->resolving('db', function ($manager) {
            $manager->resolve('mongodb', function ($config) {
                return new MongoDBConnection($config);
            });
        });
    }
}