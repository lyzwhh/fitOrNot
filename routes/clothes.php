<?php
/**
 * Created by PhpStorm.
 * User: yuse
 * Date: 19/5/19
 * Time: 下午9:47
 */
Route::group(['prefix' => 'clothes','middleware' => 'token'],function (){
    Route::get('clothes','ClothesController@getClothes');
    Route::post('clothes','ClothesController@setClothes');
});