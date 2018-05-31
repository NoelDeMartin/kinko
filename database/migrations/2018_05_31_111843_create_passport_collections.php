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
        Schema::create('oauth_auth_codes');
        Schema::create('oauth_access_tokens', function (Blueprint $collection) {
            $collection->field('user_id')->index();
        });
        Schema::create('oauth_clients', function (Blueprint $collection) {
            $collection->field('user_id')->index();
        });
        Schema::create('oauth_personal_access_clients', function (Blueprint $collection) {
            $collection->field('client_id')->index();
        });
        Schema::create('oauth_refresh_tokens', function (Blueprint $collection) {
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
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('oauth_auth_codes');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_personal_access_clients');
    }
}
