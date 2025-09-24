<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);

Route::middleware('auth:api')->group(function(){
    Route::post('logout',[AuthController::class,'logout']);
    Route::get('me',[AuthController::class,'me']);

    Route::get('companies',[CompanyController::class,'index']);
    Route::post('companies',[CompanyController::class,'store']);
    Route::get('companies/current',[CompanyController::class,'current']);
    Route::get('companies/{id}',[CompanyController::class,'show']);
    Route::put('companies/{id}',[CompanyController::class,'update']);
    Route::delete('companies/{id}',[CompanyController::class,'destroy']);
    Route::post('companies/{id}/set-active',[CompanyController::class,'setActive']);
});

