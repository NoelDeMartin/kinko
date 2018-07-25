<?php

namespace Kinko\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\Resource;

use Kinko\GraphQL\GraphQL;
use Kinko\GraphQL\GraphQLDatabaseProvider;
use Kinko\Database\MongoDB\GraphQL\Provider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Resource::withoutWrapping();

        $app = $this->app;
        Validator::extend(
            'secure_url',
            function ($attribute, $value, $parameters, $validator) use (&$app) {
                return $validator->validateUrl($attribute, $value) && (
                    !$app->environment('production') || Str::startsWith($value, 'https://')
                );
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('graphql', GraphQL::class);
        $this->app->bind(GraphQLDatabaseProvider::class, Provider::class);
        $this->app->bind(ClientInterface::class, Client::class);
    }
}
