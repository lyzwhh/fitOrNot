<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuitsRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suits_requests', function (Blueprint $table) {
            $table->increments('request_id');
            $table->integer('request_from');
            $table->integer('request_to');
            $table->integer('request_status');
            $table->string('order_msg');
            $table->string('feed_back');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suits_requests');
    }
}
