<?php

Route::view('login', 'auth.login')->name('login');
Route::post('login', 'AuthController@login');

Route::get('logout', 'AuthController@logout');

Route::middleware('auth')->group(function () {
    Route::view('/', 'app');
});
