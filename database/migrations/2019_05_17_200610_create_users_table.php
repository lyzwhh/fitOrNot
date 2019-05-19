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
            $table->string('figure')->default('未填写');
            $table->string('signature')->default('未填写');
            $table->string('avatar_url')->default('https://cdn.lyzwhh.top/avatar.jpg');
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
