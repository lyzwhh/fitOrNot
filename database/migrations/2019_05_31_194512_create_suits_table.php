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
        Schema::create('suits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('owner');
            $table->integer('count')->default(0);       // 用处未定
//            $table->integer('total_price');
//            $table->integer('per_price')->default(0);
            $table->string('title');                    //名称
            $table->string('clothes');                    //搭配结果的图片的url
            $table->string('clothes_ids')->nullable();
            $table->integer('request_id')->nullable();  //如果是请求别搭配的搭配 , 保存那个suit_request的id
            $table->string('category');
            $table->json('tags')->nullable();
            $table->string('remarks')->nullable();
            $table->string('background')->nullable();

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
