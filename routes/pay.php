<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/10/22
 * Time: 下午9:14
 */
Route::group(['prefix'  =>  'pay','middleware'   =>  'token'],function (){
//    Route::post('alipay','PayController@makeOrder');//原生SDK
//    Route::post('alipaynotify','PayController@alipaynotify');

    Route::post('alipay','PayController2@alipay');      //第三方包的实现方法
    Route::post('alipaynotify','PayController2@alipaynotify');
    Route::post('alipayreturn','PayController2@alipayreturn');

});