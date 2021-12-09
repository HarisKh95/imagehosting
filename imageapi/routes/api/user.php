<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('getCurrentUser',[UserController::class,'getUser'] );
    Route::post('updateCurrentUser',[UserController::class,'updateUser'] );
    Route::get('logoutUser',[UserController::class,'logout'] );
});

Route::get('verifyMail/{email}', [UserController::class, 'verify']);
