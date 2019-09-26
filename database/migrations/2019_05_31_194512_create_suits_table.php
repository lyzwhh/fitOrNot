<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suits', function (Blueprint $table) {   //todo 搭配师字段 , title , 分类 , tags , (使用的单品)
            $table->increments('id');
            $table->string('owner');
            $table->integer('count')->default(0);
            $table->integer('total_price');
            $table->integer('per_price')->default(0);
            $table->json('clothes');

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
        Schema::dropIfExists('suits');
    }
}
