<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\DebtPaymentController;
use App\Http\Controllers\ActivitiesLogController;
use App\Http\Controllers\ProductOriginController;
use App\Http\Controllers\ReceiptProductController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\FinancialTransactionController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\InstallmentPaymentController;

/**
 * 🔹 **Authentication Routes**
 * Includes login, logout, and JWT token refresh functionality.
 */
Route::post('/login', [AuthController::class, 'login']);      // Login route
Route::post('/logout', [AuthController::class, 'logout']);    // Logout route
Route::post('/refresh', [AuthController::class, 'refresh']);  // JWT token refresh route

/**
 * 🔹 **Get authenticated user details**
 * Requires Sanctum authentication to retrieve the authenticated user's information.
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * 🔹 **Public Routes (No Authentication Required)**
 * Allows retrieving product origins and categories without authentication.
 */
Route::get('productOrigin', [ProductOriginController::class, 'index']);  // Get product origins
Route::get('productCategory', [ProductCategoryController::class, 'index']);  // Get product categories

/**
 * 🔹 **Routes requiring JWT authentication**
 * These routes are grouped inside `Route::middleware('jwt')` to ensure secure access.
 */
Route::middleware('jwt')->group(function () {

    /** ✅ **User Management** */
    Route::apiResource('user', UserController::class)->names([
        'index' => 'user.list',      // Retrieve user list
        'store' => 'user.create',    // Create new user
        'show' => 'user.details',    // Get user details
        'update' => 'user.update',   // Update user information
        'destroy' => 'user.delete'   // Delete user
    ]);

    /** ✅ **Customer Management** */
    Route::apiResource('customer', CustomerController::class)->names([
        'index' => 'customer.list',      // Retrieve customer list
        'store' => 'customer.create',    // Add new customer
        'show' => 'customer.details',    // Get customer details
        'update' => 'customer.update',   // Update customer information
        'destroy' => 'customer.delete'   // Delete customer
    ]);

    /** ✅ **Product Management** */
    Route::apiResource('product', ProductController::class)->names([
        'index' => 'product.list',      // Retrieve product list
        'store' => 'product.create',    // Add new product
        'show' => 'product.details',    // Get product details
        'update' => 'product.update',   // Update product information
        'destroy' => 'product.delete'   // Delete product
    ]);

    /** ✅ **Product Categories Management** */
    Route::post('productCategory', [ProductCategoryController::class, 'store']);       // Create new category
    Route::put('productCategory/{productCategory}', [ProductCategoryController::class, 'update']);  // Update category
    Route::delete('productCategory/{productCategory}', [ProductCategoryController::class, 'destroy'])->name("productCategory.delete");  // Delete category

    /** ✅ **WhatsApp Messaging Routes** */
    Route::get('getmessage', [WhatsappController::class, 'index'])->name('whatsappMessage.list');  // Retrieve WhatsApp messages

    /** ✅ **Financial Reports & Activity Logs** */
    Route::get('/financialReport', [FinancialReportController::class, 'index'])->name('financialReport.list');  // Retrieve financial reports
    Route::get('/activiteLog', [ActivitiesLogController::class, 'index'])->name('activiteLog.list');  // Retrieve activity logs

    /** ✅ **Receipt Management** */
    Route::apiResource('/receipt', ReceiptController::class);  // Manage receipts

    /** ✅ **Receipt Product Management** */
    Route::get('receiptProducts/{id}', [ReceiptProductController::class, 'getreciptProduct']);  // Retrieve receipt products

    /** ✅ **Installments and Payment Management** */
    Route::post('installments/{id}/payments', [InstallmentPaymentController::class, 'store']);  // Make installment payment
    Route::post('installment/customer/{id}', [InstallmentPaymentController::class, 'installmentPaymentReceipt']);  // Pay installment for a customer
    Route::apiResource('/installmentPayments', InstallmentPaymentController::class);  // Manage installment payments

    /** ✅ **Payment Management** */
    Route::apiResource('/payment', PaymentController::class);  // Manage payments

    /** ✅ **Debt Management** */
    Route::apiResource('/debt', DebtController::class);  // Manage debts
    Route::apiResource('/debtPayments', DebtPaymentController::class);  // Manage debt payments

    /** ✅ **Customer-Specific Routes** */
    Route::get('debts/customer/{id}', [CustomerController::class, 'getCustomerDebts']);  // Retrieve customer debts
    Route::get('receiptProducts/customer/{id}', [CustomerController::class, 'getCustomerReceiptProducts']);  // Retrieve customer receipt products
    Route::get('receipt/customer/{id}', [CustomerController::class, 'getCustomerReceipt']);  // Retrieve customer receipts





    Route::apiResource('agent', AgentController::class)->names([
        'index' => 'agent.list',
        'store' => 'agent.create',
        'show' => 'agent.details',
        'update' => 'agent.update',
        'destroy' => 'agent.delete'
    ]);

    Route::get('financialtransaction/agent/{id}', [AgentController::class, 'getaAgentFinancialTransactions']);
    Route::post('financialtransaction/agent/{id}', [FinancialTransactionController::class, 'CreatePaymentFinancialTransaction']);

    Route::apiResource('/financialtransaction', FinancialTransactionController::class);



});
