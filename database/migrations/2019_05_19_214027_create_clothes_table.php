<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClothesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clothes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('owner');
            $table->string('pic_url');
            $table->string('category')->default('未填写'); //上装1，下装2，鞋子3，配饰4 (discarded)   //大类-小类 如 上衣-卫衣 (now)
            $table->string('brand')->default('未填写');
            $table->string('color')->default('未填写');
            $table->string('price')->default('未填写');
            $table->json('tags')->nullabe();
            $table->string('remarks')->nullable();    //备注

            $table->integer('count')->default(0);       // todo 去除穿衣函数 , 创建搭配衣服的时候自增
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
        Schema::dropIfExists('clothes');
    }
}
