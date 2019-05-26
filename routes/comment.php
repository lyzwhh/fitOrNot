<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/26
 * Time: 下午3:46
 */
Route::group(['prefix'  =>'comment','middleware'    =>  'token'],function (){
    Route::post('');
});