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
    Route::get('getonesmoment/{openid}','MomentController@getMomentByOpenid');
    Route::delete('moment/{id}','MomentController@deleteMoment');


    Route::get('/like/{id}','MomentController@createLike')->middleware('token');
    Route::get('/checkIfLiked/{id}','MomentController@checkIfLiked')->middleware('token');
    Route::delete('/like/{id}','MomentController@deletelike')->middleware('token');
    Route::get('/allLiked','MomentController@sth')->middleware('token');            //TODO::看情况实装


    Route::post('/comment','MomentController@createComment')->middleware('token');
    Route::delete('/comment/{commentId}','MomentController@deleteComment')->middleware('token');     //为评论的id
    Route::get('/comment/{momentId}','MomentController@getCommentByMoment')->middleware('token');

});