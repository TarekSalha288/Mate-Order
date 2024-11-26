<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\TowFactor;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});
Route::post('verify',[AuthController::class,'verify']);

Route::group([
    'middleware' => [TowFactor::class,'api'],
], function ($router) {
    Route::put('/updateFirstName', [UserController::class, 'updateFirstName']);
    Route::put('/updateLastName', [UserController::class, 'updateLastName']);
    Route::put('/updatePhone', [UserController::class, 'updatePhone']);
    Route::put('/updateImage', [UserController::class, 'updateImage']);
    Route::put('/updatePassword', [UserController::class, 'updatePassword']);
});
