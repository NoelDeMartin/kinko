<?php

Route::middleware('api')->group(function () {
    Route::namespace('\Laravel\Passport\Http\Controllers')->group(function () {
        Route::post('token', 'AccessTokenController@issueToken')->middleware('throttle');
    });

    Route::namespace('\Kinko\Http\Controllers\Store\Api')->group(function () {
        // TODO rename this to "register" to comply with RFC 7591
        Route::post('clients', 'ClientsController@store')->name('store.clients');

        Route::any('/', 'GraphQLController')->middleware('auth:api');
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

