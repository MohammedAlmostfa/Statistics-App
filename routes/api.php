<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Route to get authenticated user details
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Login route
Route::post('/login', [AuthController::class, 'login']);

// Group routes that require JWT middleware
Route::middleware('jwt')->group(function () {
    Route::apiResource('/user', UserController::class);
    Route::post('/user/{user}/updatestatus', [UserController::class, 'updateUserStatus']);

});
