<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SuperUserController;
use App\Http\Middleware\AdminMiddleWare;
use App\Http\Middleware\SuperUserMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\PasswordResetController;
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
    Route::post('/me', [AuthController::class, 'me'])->middleware(['auth:api', TowFactor::class])->name('me');
});
Route::post('verify', [AuthController::class, 'verify']);

Route::group([
    'middleware' => [TowFactor::class, 'api', 'auth'],
], function ($router) {
    Route::post('password/request', [PasswordResetController::class, 'sendConfirmationEmail']);  // Send email
    Route::get('password/confirm/{token}', [PasswordResetController::class, 'confirmReset']); // Confirm password reset
    Route::post('password/reset/{token}', [PasswordResetController::class, 'resetPassword']);
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::put('update', [UserController::class, 'updateInfo']);
    Route::delete('/deleteImage', [UserController::class, 'deleteImage']);
    Route::put('/updatePassword', [UserController::class, 'updatePassword']);
    Route::put('/updateImage', [UserController::class, 'updateImage']);
    Route::post('/addAddress', [UserController::class, 'addAddress']);
    Route::get('/resendCode', [AuthController::class, 'resendCode']);
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::post('createStore', [AdminController::class, 'createStore'])->middleware(AdminMiddleWare::class);
    Route::delete('deleteAccount', [AdminController::class, 'deleteAccount'])->middleware(AdminMiddleWare::class);
    Route::put('updateStore/{id}', [AdminController::class, 'updateStore'])->middleware(AdminMiddleWare::class);
    Route::get('editStore/{id}', [AdminController::class, 'edit'])->middleware(AdminMiddleWare::class);
    Route::delete('deleteStore/{id}', [AdminController::class, 'deleteStore'])->middleware(AdminMiddleWare::class);
    Route::get('allStores', [AdminController::class, 'stores'])->middleware(AdminMiddleWare::class);
});
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::middleware([SuperUserMiddleware::class, 'api', 'auth',TowFactor::class])->group(function () {
    Route::post('createProduct', [SuperUserController::class, 'createProduct']);
    Route::get('getAllProductInStore', [SuperUserController::class, 'getAllProductInStore']);
    Route::put('updateProductInStore/{id}', [SuperUserController::class, 'updateProductInStore']);
    Route::delete('deleteProductInStore/{id}', [SuperUserController::class, 'deleteProductInStore']);
});
