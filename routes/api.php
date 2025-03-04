<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TripsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::get('/driver', [DriverController::class, 'show']);
    Route::post('/driver', [DriverController::class, 'update']);

    Route::post('/trips', [TripsController::class, 'store']);  
    Route::get('/trips/{trip}', [TripsController::class, 'show']);
    Route::get('/trips/{trip}/accept', [TripsController::class, 'accept']);
    Route::get('/trips/{trip}/start', [TripsController::class, 'start']);
    Route::get('/trips/{trip}/end', [TripsController::class, 'end']);
    Route::get('/trips/{trip}/location', [TripsController::class, 'location']);
});

Route::post('/login', [LoginController::class, 'login']);
Route::post('login/verify', [LoginController::class, 'verify']);
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout']);