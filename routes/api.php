<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;

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

// Group routes that require JWT middleware
Route::middleware('jwt')->group(function () {
    Route::apiResource('/user', UserController::class);
    Route::apiResource('/product', ProductController::class);
    Route::apiResource('/customer', CustomerController::class);

    Route::post('/user/{user}/updatestatus', [UserController::class, 'updateUserStatus']);

});
