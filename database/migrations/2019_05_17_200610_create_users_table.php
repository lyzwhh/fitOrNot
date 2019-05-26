<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid');
            $table->string('session_key');
            $table->string('phone')->default('未填写');
            $table->string('nickname')->default('未填写');
            $table->string('height')->default('未填写');
            $table->string('weight')->default('未填写');
            $table->string('signature')->default('未填写');
            $table->integer('liked')->default(0);
            $table->string('avatar_url')->default('https://cdn.lyzwhh.top/avatar.jpg');

            $table->integer('total')->default(0); //衣服数量
            $table->integer('vip')->default(0); // 0 为免费会员
            $table->integer('hide_figure')->default(0);

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
        Schema::dropIfExists('users');
    }
}
