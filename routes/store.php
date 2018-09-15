<?php

Route::middleware('api')->group(function () {
    Route::namespace('\Kinko\Http\Controllers\Store\Api')->group(function () {
        Route::post('token', 'AccessTokenController@issueToken')->middleware('throttle');

        // TODO rename this to "register" to comply with RFC 7591
        Route::post('clients', 'ClientsController@store')->name('store.clients');

        Route::any('/', 'GraphQLController')->middleware('auth:api');
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::namespace('\Kinko\Http\Controllers\Store\Web')->group(function () {
        Route::get('authorize', 'AuthorizationController@create');
        Route::post('authorize', 'AuthorizationController@approve')->name('store.authorize.approve');
        Route::delete('authorize', 'AuthorizationController@deny')->name('store.authorize.deny');
    });
});

