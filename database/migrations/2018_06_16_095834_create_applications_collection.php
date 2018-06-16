<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Kinko\Database\Schema\NonRelationalBlueprint as Blueprint;

class CreateApplicationsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applications', function (Blueprint $collection) {
            $collection->field('name')->unique();
            $collection->field('domain')->unique();
            $collection->field('client_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applications');
    }
}
