<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/17
 * Time: 下午4:16
 */

Route::group(['prefix' => 'user'],function (){
    Route::post('/code2session','UserController@code2session');
    Route::post('/userInfo','UserController@setUserInfo')->middleware('token');
    Route::get('/userInfo','UserController@getUserInfo')->middleware('token');
});
