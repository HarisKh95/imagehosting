<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('imageUpload',[ImageController::class,'upload']);
    Route::post('imageUpdate/{id}',[ImageController::class,'imageUpdate']);
    Route::delete('imageRemove/{id}',[ImageController::class,'imageRemove']);
    Route::get('getAllImage',[ImageController::class,'getAllImages']);
    Route::get('getAllPublicImage',[ImageController::class,'getAllPublicImages']);
    Route::get('getAllPrivateImage',[ImageController::class,'getAllPrivateImages']);
    Route::get('getAllHiddenImage',[ImageController::class,'getAllHiddenImages']);
    Route::get('searchImage',[ImageController::class,'search']);
});



