<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
           Schema::create('link_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('link_id');
            $table->integer('tag_id');
            $table->nullableTimestamps();            

            $table->foreign('link_id')
                  ->references('id')->on('links')
                  ->onDelete('cascade');

            $table->foreign('tag_id')
                  ->references('id')->on('tags')
                  ->onDelete('cascade');
        });  
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('link_tag');
    }
}
