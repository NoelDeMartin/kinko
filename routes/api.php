<?php

// TODO this authentication should need admin scopes
Route::middleware('auth:api')->group(function () {
    Route::get('collections', 'CollectionsController@index');
    Route::get('clients', 'ClientsController@index');
});
