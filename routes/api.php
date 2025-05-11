<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ActivitiesLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\ProductOriginController;
use App\Http\Controllers\ReceiptProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\InstallmentPaymentController;
use App\Http\Controllers\PaymentController;

// Import models
use App\Models\ActivitiesLog;
use App\Models\Payment;

/**
 * Get authenticated user details
 * This route fetches the authenticated user's information using Sanctum authentication.
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Authentication routes
 * These include login, logout, and JWT token refresh.
 */
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);

/**
 * Product origins and categories
 * Fetch product origins and categories.
 */
Route::get('productOrigin', [ProductOriginController::class, 'index']);
Route::get('productCategory', [ProductCategoryController::class, 'index']);

/**
 * Grouped routes requiring JWT authentication
 * These routes require the user to be authenticated using JWT.
 */
Route::middleware('jwt')->group(function () {

    // User management
    Route::post('/user/{user}/updatestatus', [UserController::class, 'updateUserStatus'])->name('user.change_status');
    Route::apiResource('user', UserController::class)->names([
        'index' => 'user.list',
        'store' => 'user.create',
        'show' => 'user.details',
        'update' => 'user.update',
        'destroy' => 'user.delete'
    ]);

    // Customer management
    Route::apiResource('customer', CustomerController::class)->names([
        'index' => 'customer.list',
        'store' => 'customer.create',
        'show' => 'customer.details',
        'update' => 'customer.update',
        'destroy' => 'customer.delete'
    ]);

    // Product management
    Route::apiResource('product', ProductController::class)->names([
        'index' => 'product.list',
        'store' => 'product.create',
        'show' => 'product.details',
        'update' => 'product.update',
        'destroy' => 'product.delete'
    ]);

    // Product categories management
    Route::post('productCategory', [ProductCategoryController::class, 'store']);
    Route::put('productCategory/{productCategory}', [ProductCategoryController::class, 'update']);
    Route::delete('productCategory/{productCategory}', [ProductCategoryController::class, 'destroy'])->name("productCategory.delete");

    // WhatsApp messaging routes
    Route::get('getmessage', [WhatsappController::class, 'index'])->name('whatsappMessage.list');

    // Financial reports and activity logs
    Route::get('/financialReport', [FinancialReportController::class, 'index'])->name('financialReport.list');
    Route::get('/activiteLog', [ActivitiesLogController::class, 'index'])->name('activiteLog.list');

    // Receipt management
    Route::apiResource('/receipt', ReceiptController::class);
    Route::get('receipt/customer/{id}', [ReceiptController::class, 'getCustomerReceipt']);

    // Receipt product management
    Route::get('receiptProducts/customer/{id}', [ReceiptProductController::class, 'index']);
    Route::get('receiptProducts/{id}', [ReceiptProductController::class, 'getreciptProduct']);

    // Installment and payment management
    Route::post('installments/{id}/payments', [InstallmentPaymentController::class, 'store']);
    Route::apiResource('/installmentPayments', InstallmentPaymentController::class);
    Route::apiResource('/payment', PaymentController::class);
});
