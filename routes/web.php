<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| containing the "web" middleware group.
|
*/

// Ruta de la Landing Page (Pública)
Route::get('/', function () {
    return view('landingpage');
});

// Rutas de Autenticación generadas por Laravel UI
Auth::routes();

// Rutas del Dashboard (Soporta tanto /home como /dashboard)
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard')->middleware('auth');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');