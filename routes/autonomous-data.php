<?php

Route::middleware('api')->group(function () {
    Route::namespace('\Laravel\Passport\Http\Controllers')->group(function () {
        Route::post('token', 'AccessTokenController@issueToken')->middleware('throttle');
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::namespace('\Laravel\Passport\Http\Controllers')->group(function () {
        Route::get('authorize', 'AuthorizationController@authorize');
        Route::post('authorize', 'ApproveAuthorizationController@approve');
        Route::delete('authorize', 'DenyAuthorizationController@deny');
    });

    Route::namespace('\Kinko\Http\Controllers\Web\AutonomousData')->group(function () {
        Route::get('register', 'ClientRegistrationController@create');
        Route::post('register', 'ClientRegistrationController@store');
    });
});
