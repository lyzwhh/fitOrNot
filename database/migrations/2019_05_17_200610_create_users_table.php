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
            $table->increments('user_id');
            $table->string('openid')->nullable();
            $table->string('session_key')->nullable();
            $table->string('phone')->default('未填写');
            $table->string('nickname')->default('未填写');
            $table->string('birth_year')->default('点击填写');      //注意和前端的输入输出皆为年龄 , 转换代码用"age to year" "year to age"全局搜索
            $table->string('height')->default('点击填写');
            $table->string('weight')->default('点击填写');
            $table->string('signature')->default('点击填写');
            $table->integer('liked')->default(0);
            $table->string('avatar_url')->default('http://cdn.lyzwhh.top/avatar.jpg');

            $table->integer('total')->default(0); //衣服数量
            $table->integer('vip')->default(0); // 0 为免费会员
            $table->integer('hide_figure')->default(1);

            $table->integer('followers')->default(0);   //关注他的人
            $table->integer('following')->default(0);   //关注别人的数目

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
