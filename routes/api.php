<?php

use Illuminate\Http\Request;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\ProductOriginController;
use App\Http\Controllers\ReceiptProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\InstallmentPaymentController;

// Route to get authenticated user details
// This route is used to fetch the authenticated user's details using Sanctum
Route::get('/user', function (Request $request) {
    return $request->user(); // Returns the authenticated user's data
})->middleware('auth:sanctum'); // Requires the user to be authenticated with Sanctum

// Login route
// This route is used to log in the user by submitting their credentials
Route::post('/login', [AuthController::class, 'login']); // Logs the user in

// User logout
// This route is used to log out the user
Route::post('logout', [AuthController::class, 'logout']); // Logs the user out

// Refresh JWT token
// This route allows the user to refresh their JWT token if it has expired
Route::post('refresh', [AuthController::class, 'refresh']); // Refreshes the user's JWT token

// Basic routes for ProductOrigin and ProductCategory
// These routes are used to fetch product origins and categories
Route::get('productOrigin', [ProductOriginController::class, 'index']); // Displays all product origins
Route::get('productCategory', [ProductCategoryController::class, 'index']); // Displays all product categories

// Group routes that require JWT middleware
// These routes require the user to be authenticated using JWT
Route::middleware('jwt')->group(function () {
    // User-related routes
    // These routes handle CRUD operations for users
    Route::apiResource('/user', UserController::class); // Displays, creates, updates, and deletes users

    // Product-related routes
    // These routes handle CRUD operations for products
    Route::apiResource('/product', ProductController::class); // Displays, creates, updates, and deletes products

    // Customer-related routes
    // These routes handle CRUD operations for customers
    Route::apiResource('/customer', CustomerController::class); // Displays, creates, updates, and deletes customers

    // Receipt-related routes
    // These routes handle CRUD operations for receipts
    Route::apiResource('/receipt', ReceiptController::class); // Displays, creates, updates, and deletes receipts

    Route::get('receipt/customer/{id}', [ReceiptController::class, 'getCustomerReceipt']);
    // Product Category routes (custom)
    // These routes manage product categories
    Route::post('productCategory', [ProductCategoryController::class, 'store']); // Creates a new product category
    Route::put('productCategory/{productCategory}', [ProductCategoryController::class, 'update']); // Updates an existing product category
    Route::delete('productCategory/{productCategory}', [ProductCategoryController::class, 'destroy']); // Deletes a product category

    // Update user status
    // This route updates the status of a user
    Route::post('/user/{user}/updatestatus', [UserController::class, 'updateUserStatus']); // Updates the user's status

    // Whatsapp messaging route
    // This route retrieves Whatsapp messages
    Route::get('getmessage', [WhatsappController::class, 'index']); // Fetches all Whatsapp messages sent

    // Receipt Product Controller route
    // This route fetches receipt products based on a customer ID
    Route::get('receiptProductController/customer/{id}', [ReceiptProductController::class, 'index']); // Displays receipt products for a specific customer

    // Installment Payment routes
    // These routes handle installment payments
    Route::post('installments/{id}/payments', [InstallmentPaymentController::class, 'store']); // Creates a new installment payment associated with the installment
    Route::put('installmentPayments/{id}', [InstallmentPaymentController::class, 'update']); // Updates an existing installment payment by ID
});
