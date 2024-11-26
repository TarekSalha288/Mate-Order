<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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
Route::group([
    'middlware' => 'api',
], function ($router) {
    Route::put('/updateFirstName/{id}', [UserController::class, 'updateFirstName']);
    Route::put('/updateLastName/{id}', [UserController::class, 'updateLastName']);
    Route::put('/updatePhone/{id}', [UserController::class, 'updatePhone']);
    Route::put('/updateImage/{id}', [UserController::class, 'updateImage']);
    Route::put('/updatePassword/{id}', [UserController::class, 'updatePassword']);
});