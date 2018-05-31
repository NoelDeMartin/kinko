<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Kinko\Database\Schema\NonRelationalBlueprint as Blueprint;

class CreateCollectionsCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collections', function (Blueprint $collection) {
            $collection->field('name')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collections');
    }
}
