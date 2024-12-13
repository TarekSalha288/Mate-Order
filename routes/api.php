<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SuperUserController;
use App\Http\Middleware\AdminMiddleWare;
use App\Http\Middleware\SuperUserMiddleware;
use App\Http\Middleware\UserMiddleWare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
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
Route::get('/resendCode', [AuthController::class, 'resendCode']);
//every one can visit this routes
Route::group([
    'middleware' => [TowFactor::class, 'api', 'auth'],
], function ($router) {
    Route::post('password/request', [PasswordResetController::class, 'sendConfirmationEmail']);  // Send email
    Route::get('password/confirm/{token}', [PasswordResetController::class, 'confirmReset']); // Confirm password reset
    Route::post('password/reset/{token}', [PasswordResetController::class, 'resetPassword']);
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::put('update', [UserController::class, 'updateInfo']);
    Route::put('/updatePassword', [UserController::class, 'updatePassword']);
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
});
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::group(['middleware' => [SuperUserMiddleware::class, 'api', 'auth', TowFactor::class]], function () {
    Route::post('createProduct', [SuperUserController::class, 'createProduct']);
    Route::get('getAllProductInStore', [SuperUserController::class, 'getAllProductInStore']);
    Route::put('updateProductInStore/{id}', [SuperUserController::class, 'updateProductInStore']);
    Route::delete('deleteProductInStore/{id}', [SuperUserController::class, 'deleteProductInStore']);
    Route::post('acceptReceiving/{id}', [SuperUserController::class, 'acceptReceiving']);
    Route::delete('rejectReceiving/{id}', [SuperUserController::class, 'rejectReceiving']);
    Route::post('acceptSending/{id}', [SuperUserController::class, 'acceptSending']);
    Route::delete('rejectSending/{id}', [SuperUserController::class, 'rejectSending']);
    Route::get('waiting', [SuperUserController::class, 'waitingOrders']);
    Route::get('sending', [SuperUserController::class, 'sendingOrders']);
    Route::get('receiving', [SuperUserController::class, 'receivingOrders']);
    Route::get('archive', [SuperUserController::class, 'archive']);
    Route::get('notifications', [SuperUserController::class, 'notifications']);
    Route::put('refreshData', [SuperUserController::class, 'refreshData']);
});
Route::group([
    'middleware' => [TowFactor::class, UserMiddleWare::class, 'api', 'auth'],
], function ($router) {
    Route::put('/updateImage', [UserController::class, 'updateImage']);
    Route::post('/addAddress', [UserController::class, 'addAddress']);
    Route::delete('/deleteImage', [UserController::class, 'deleteImage']);
    Route::get('showImage',[UserController::class,'showPhoto']);
    Route::get('showAddresses', [UserController::class, 'showAddresses']);
    Route::get('notifications', [UserController::class, 'notifications']);
    Route::get('showFav', [UserController::class, 'showFav']);
    /////////////////////////////////////////////////////////////////////////////////
    Route::post('addFav/{id}', [ProductController::class, 'addFavorite']);
    Route::post('disFav/{id}', [ProductController::class, 'disFavorite']);
    Route::get('showProducts/{category}', [ProductController::class, 'showProducts']);
    Route::get('allProducts', [ProductController::class, 'allProducts']);
    Route::get('searchProduct', [ProductController::class, 'searchProduct']);
    //////////////////////////////////////////////////////////////////////////////
    Route::get('showStores', [StoreController::class, 'show']);
    Route::get('searchStore', [StoreController::class, 'searchStore']);
    Route::get('storeProducts/{id}', [StoreController::class, 'edit']);
    ////////////////////////////////////////////////////////////////////////////////////
    Route::post('addOrder/{Product_id}/{adress_id}', [OrderController::class, 'addOrder']);
    Route::get('getAllOrderInCart', [OrderController::class, 'getAllOrdersInCart']);
    Route::put('updateOrder/{order_id}/{adress_id}', [OrderController::class, 'updateOrder']);
    Route::delete('deleteOrder/{order_id}', [OrderController::class, 'deleteOrder']);
});
Route::group([
    'middleware' => [TowFactor::class, AdminMiddleWare::class, 'api', 'auth'],
], function ($router) {
    Route::post('createStore', [AdminController::class, 'createStore']);
    Route::delete('deleteAccount', [AdminController::class, 'deleteAccount']);
    Route::put('updateStore/{id}', [AdminController::class, 'updateStore']);
    Route::get('editStore/{id}', [AdminController::class, 'edit']);
    Route::delete('deleteStore/{id}', [AdminController::class, 'deleteStore']);
    Route::get('allStores', [AdminController::class, 'stores']);
});
