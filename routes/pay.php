<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/10/22
 * Time: 下午9:14
 */
Route::group(['prefix'  =>  'pay','middleware'   =>  'token'],function (){
    Route::post('alipay','PayController@makeOrder');
    Route::post('alipaynotify','PayController@alipaynotify');

});