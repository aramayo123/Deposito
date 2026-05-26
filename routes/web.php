<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RunQueuedJobsController;
use App\Http\Controllers\ProductEntryController;
use App\Http\Controllers\ProductExitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAlertController;
use Illuminate\Support\Facades\Route;

Route::get('/run-queue', RunQueuedJobsController::class)->name('queue.run-cron');

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::post('products/{product}/photo', [ProductController::class, 'updatePhoto'])->name('products.photo');
Route::patch('products/{product}/damaged', [ProductController::class, 'updateDamaged'])->name('products.damaged');
Route::resource('products', ProductController::class);

Route::get('entries', [ProductEntryController::class, 'index'])->name('entries.index');
Route::get('entries/create', [ProductEntryController::class, 'create'])->name('entries.create');
Route::post('entries', [ProductEntryController::class, 'store'])->name('entries.store');
Route::get('entries/{entry}', [ProductEntryController::class, 'show'])->name('entries.show');

Route::get('exits', [ProductExitController::class, 'index'])->name('exits.index');
Route::get('exits/create', [ProductExitController::class, 'create'])->name('exits.create');
Route::post('exits', [ProductExitController::class, 'store'])
    ->middleware('ensure.sufficient.stock')
    ->name('exits.store');
Route::get('exits/{product_exit}', [ProductExitController::class, 'show'])->name('exits.show');

Route::get('reports/global', [ReportController::class, 'global'])->name('reports.global');

Route::resource('deposits', DepositController::class);

Route::patch('stock-alerts/{stock_alert}/read', [StockAlertController::class, 'markRead'])->name('stock-alerts.read');
