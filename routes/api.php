<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('auth/login', [AuthController::class, 'login']);
Route::get('tracking/{orderNumber}', [TransactionController::class, 'tracking']);

// Protected routes
Route::middleware(['jwt.auth'])->group(function () {

    // Auth
    Route::post('auth/logout',  [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/me',       [AuthController::class, 'me']);

    // Dashboard
    Route::get('dashboard', [TransactionController::class, 'dashboard']);

    // Users — Admin only
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('users', UserController::class);
    });
    Route::get('technicians', [UserController::class, 'technicians']); // admin + cashier

    // Products — Admin only for CUD, read for all
    Route::get('products/all',        [ProductController::class, 'all']);
    Route::get('products/categories', [ProductController::class, 'categories']);
    Route::get('products',            [ProductController::class, 'index']);
    Route::get('products/{product}',  [ProductController::class, 'show']);
    Route::middleware(['role:admin'])->group(function () {
        Route::post('products',             [ProductController::class, 'store']);
        Route::put('products/{product}',    [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
    });

    // Transactions
    Route::get('transactions',                              [TransactionController::class, 'index']);
    Route::get('transactions/{transaction}',               [TransactionController::class, 'show']);
    Route::get('transactions/{transaction}/invoice',       [TransactionController::class, 'invoice']);
    Route::patch('transactions/{transaction}/status',      [TransactionController::class, 'updateStatus']);

    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::post('transactions',                        [TransactionController::class, 'store']);
        Route::put('transactions/{transaction}',           [TransactionController::class, 'update']);
    });
    Route::middleware(['role:admin'])->group(function () {
        Route::delete('transactions/{transaction}',        [TransactionController::class, 'destroy']);
    });
});
