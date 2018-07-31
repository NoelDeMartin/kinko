<?php

Route::middleware('api')->group(function () {
    Route::namespace('\Laravel\Passport\Http\Controllers')->group(function () {
        Route::post('token', 'AccessTokenController@issueToken')->middleware('throttle');
    });

    Route::namespace('\Kinko\Http\Controllers\Store\Api')
        ->middleware('auth:api')
        ->group(function () {
            Route::any('/', 'GraphQLController');
        });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::namespace('\Laravel\Passport\Http\Controllers')->group(function () {
        Route::get('authorize', 'AuthorizationController@authorize');
        Route::post('authorize', 'ApproveAuthorizationController@approve')->name('store.authorize.approve');
        Route::delete('authorize', 'DenyAuthorizationController@deny')->name('store.authorize.deny');
    });

    Route::namespace('\Kinko\Http\Controllers\Store\Web')->group(function () {
        Route::get('register', 'ApplicationsController@create');
        Route::post('register', 'ApplicationsController@store')->name('store.register');
    });
});

Route::post('token', [
    'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken',
    'middleware' => 'throttle',
]);
