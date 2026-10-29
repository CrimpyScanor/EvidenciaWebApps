<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Authentication is intentionally not required for this academic evidence.
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

Route::get('/orders/archived', [OrderController::class, 'archived']);
Route::get('/orders/invoice/{invoice}', [OrderController::class, 'showByInvoice']);
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
Route::match(['put', 'patch'], '/orders/{order}', [OrderController::class, 'update']);
Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
