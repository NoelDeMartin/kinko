<?php

namespace Kinko\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\Resource;

use Kinko\GraphQL\GraphQL;
use Kinko\GraphQL\GraphQLDatabaseBridge;
use Kinko\Database\MongoDB\GraphQL\Bridge;

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
        $this->app->bind(GraphQLDatabaseBridge::class, Bridge::class);
        $this->app->bind(ClientInterface::class, Client::class);
    }
}
