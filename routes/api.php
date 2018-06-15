<?php

Route::middleware('auth:api')->group(function () {
    Route::get('collections', 'CollectionsController@index');

    Route::get('applications/validate', 'ApplicationValidationsController');
});
