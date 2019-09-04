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
    Route::get('/othersInfo/{user_id}','UserController@getOthersInfo')->middleware('token');
    Route::post('/setName','UserController@setName')->middleware('token');


    Route::get('/follow/{user_id}','UserController@createFollow')->middleware('token');
    Route::delete('/follow/{user_id}','UserController@deleteFollow')->middleware('token');
    Route::get('/allFollowed','UserController@getAllFollowed')->middleware('token');
    Route::get('/checkIfFollowed/{user_id}','UserController@checkIfFollowed')->middleware('token');

    Route::get('/getNicknameByUserId/{user_id}','UserController@getNicknameByUserId')->middleware('token');

    Route::get('/getConfig','UserController@getConfig')->middleware('token');
    Route::get('/setConfig/{choice}','UserController@setConfig')->middleware('token');

    Route::post('/getVCode','UserController@getVCode');
});
