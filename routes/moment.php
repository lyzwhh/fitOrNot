<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/26
 * Time: 下午3:51
 */
Route::group(['prefix'  =>  'moment','middleware'   =>  'token'],function (){
    Route::post('moment','MomentController@createMoment');
    Route::get('moment','MomentController@getMoment');
    Route::get('moment_detail/{id}','MomentController@getMomentDetail');
});