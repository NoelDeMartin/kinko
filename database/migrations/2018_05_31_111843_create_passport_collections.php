<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Kinko\Database\Schema\NonRelationalBlueprint as Blueprint;

class CreatePassportCollections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_codes', function (Blueprint $collection) {
            $collection->field('id')->unique();
            $collection->field('client_id')->index();
            $collection->field('user_id')->index();
        });
        Schema::create('access_tokens', function (Blueprint $collection) {
            $collection->field('id')->unique();
            $collection->field('user_id')->index();
        });
        Schema::create('clients', function (Blueprint $collection) {
            $collection->field('user_id')->index();
        });
        Schema::create('refresh_tokens', function (Blueprint $collection) {
            $collection->field('access_token_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
        Schema::dropIfExists('auth_codes');
        Schema::dropIfExists('access_tokens');
        Schema::dropIfExists('refresh_tokens');
    }
}
