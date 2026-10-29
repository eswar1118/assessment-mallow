<?php

use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// Order creation with concurrency-safe stock deduction
Route::post('/orders', [OrderController::class, 'store']);

// Endpoint A: Customer order history lookup by email
Route::get('/customers/orders', [CustomerOrderController::class, 'index']);

// Endpoint B: Inventory low-stock report with configurable threshold
Route::get('/products/low-stock', [ProductController::class, 'lowStock']);

// Product catalog list for retail counter dropdown
Route::get('/products', [ProductController::class, 'index']);
