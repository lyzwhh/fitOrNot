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
    Route::get('/othersInfo/{openid}','UserController@getOthersInfo')->middleware('token');

    Route::get('/follow/{openid}','UserController@createFollow')->middleware('token');
    Route::delete('/follow/{openid}','UserController@deleteFollow')->middleware('token');
    Route::get('/allFollowed','UserController@getAllFollowed')->middleware('token');
    Route::get('/checkIfFollowed/{openid}','UserController@checkIfFollowed')->middleware('token');

    Route::get('/getNicknameByOpenid/{openid}','UserController@getNicknameByOpenid')->middleware('token');

    Route::get('/getConfig','UserController@getConfig')->middleware('token');
});
