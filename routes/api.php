<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOriginController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappController;
use Database\Seeders\ProductSeeder;

// Route to get authenticated user details
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Login route
Route::post('/login', [AuthController::class, 'login']);
// User logout
Route::post('logout', [AuthController::class, 'logout']); // Logs out the authenticated user

// Refresh JWT token
Route::post('refresh', [AuthController::class, 'refresh']); // Refreshes the JWT token
Route::get('productOrigin', [ProductOriginController::class, 'index']);
Route::get('productCategory', [ProductCategoryController::class, 'index']);


// Group routes that require JWT middleware

Route::middleware('jwt')->group(function () {
    Route::apiResource('/user', UserController::class);
    Route::apiResource('/product', ProductController::class);
    Route::apiResource('/customer', CustomerController::class);
    Route::apiResource('/receipt', ReceiptController::class);

    Route::post('productCategory', [ProductCategoryController::class, 'store']);
    Route::put('productCategory/{productCategory}', [ProductCategoryController::class, 'update']);
    Route::delete('productCategory/{productCategory}', [ProductCategoryController::class, 'destroy']);
    Route::post('/user/{user}/updatestatus', [UserController::class, 'updateUserStatus']);
    Route::get('getmessage', [WhatsappController::class, 'index']);

});
