<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WorkspaceOrderController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('app', ['page' => 'login']);
    })->name('login');

    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('app', ['page' => 'dashboard']);
    })->name('dashboard');

    Route::get('/api/me', function () {
        return response()->json([
            'user' => Auth::user()->only(['id', 'name', 'email', 'role', 'is_active']),
        ]);
    });

    Route::get('/api/workspace/orders', [WorkspaceOrderController::class, 'index'])
        ->middleware('role:Administrator,Sales,Purchasing,Warehouse,Route');

    Route::post('/api/workspace/orders', [WorkspaceOrderController::class, 'store'])
        ->middleware('role:Administrator,Sales');

    Route::patch('/api/workspace/orders/{order}', [WorkspaceOrderController::class, 'update'])
        ->middleware('role:Administrator,Sales,Purchasing,Warehouse,Route');

    Route::middleware('role:Administrator')->prefix('api/employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::put('/{user}', [EmployeeController::class, 'update']);
        Route::patch('/{user}/status', [EmployeeController::class, 'updateStatus']);
    });
});

Route::get('/api/orders', [OrderController::class, 'search']);
