<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMutationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.submit');
});

Route::middleware(['auth', 'role:admin,staff'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);

    Route::get('/transactions', [StockMutationController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [StockMutationController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [StockMutationController::class, 'store'])->name('transactions.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
