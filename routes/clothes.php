<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/19
 * Time: 下午9:47
 */
Route::group(['prefix' => 'clothes','middleware' => 'token'],function (){
    Route::get('clothes','ClothesController@getClothes');
    Route::get('clothes2','ClothesController@getClothes2');      //前端内部吵架的结果
    Route::post('clothes','ClothesController@setClothes');
    Route::put('clothes','ClothesController@updateClothes');
    Route::delete('clothes/{id}','ClothesController@deleteClothes');

    Route::post('suit','ClothesController@setSuit');
    Route::get('suit','ClothesController@getSuit');
    Route::delete('suit/{suitId}','ClothesController@deleteSuit');
    Route::get('suit/wear/{suitId}','ClothesController@wearSuit');

});