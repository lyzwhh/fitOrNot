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
    Route::post('clothes/like','ClothesController@getClothesByWord');
    Route::post('clothes','ClothesController@setClothes');
    Route::put('clothes','ClothesController@updateClothes');
    Route::delete('clothes/{id}','ClothesController@deleteClothes');

    Route::post('suit','ClothesController@setSuit');
    Route::get('suit','ClothesController@getSuit');
    Route::delete('suit/{suitId}','ClothesController@deleteSuit');
    Route::get('suit/wear/{suitId}','ClothesController@wearSuit');
    Route::post('suit/like','ClothesController@getSuitByWord');

    Route::post('SR','ClothesController@createSRequest');
    Route::get('SR/SRing','ClothesController@getAllMySRing');
    Route::get('SR/SRed','ClothesController@getAllMySRed');
    Route::get('SR/clothes/{request_id}','ClothesController@getAllClothesBySR');
    Route::post('SR/clothes','ClothesController@setSuitBySR');
    Route::get('SR/suit/{request_id}','ClothesController@getSuitBySR');

});