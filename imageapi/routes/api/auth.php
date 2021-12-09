<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;




Route::post('signup',[AuthController::class,'register'] );
Route::post('signin',[AuthController::class,'login'] );
Route::post('forgetPassword',[AuthController::class,'forgetPassword'] );
Route::put('{email}/{token}',[AuthController::class,'updatepassword'] );
