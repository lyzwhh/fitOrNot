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
    Route::get('getonesmoment/{user_id}','MomentController@getMomentByUserId');
    Route::delete('moment/{id}','MomentController@deleteMoment');
    Route::get('allLikedMoment','MomentController@getAllMylikedMoment');
    Route::get('allFollowingMoment','MomentController@getAllMyFollowingMoment');

    Route::get('refresh/{id}','MomentController@refreshMoment');

    Route::get('/like/{id}','MomentController@createLike');
    Route::get('/checkIfLiked/{id}','MomentController@checkIfLiked');
    Route::delete('/like/{id}','MomentController@deletelike');


    Route::post('/comment','MomentController@createComment');
    Route::delete('/comment/{commentId}','MomentController@deleteComment');     //为评论的id
    Route::get('/comment/{momentId}','MomentController@getCommentByMoment');

});