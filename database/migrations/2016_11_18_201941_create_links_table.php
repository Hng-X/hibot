<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('team_id');
            $table->string('url');
            $table->string('title')->nullable();
            $table->string('user_id');
            $table->string('channel_id');
            $table->nullableTimestamps();
            
            $table->index('user_id');
            $table->index('channel_id');
            $table->index('title');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('links');
    }
}

