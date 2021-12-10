<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::post('imageUpload',[ImageController::class,'upload']);
    // Route::post('removeImage',[ImageController::class,'remove']);
    // Route::post('getAllImage',[ImageController::class,'getAll']);
});



