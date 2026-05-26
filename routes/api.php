<?php

use App\Http\Controllers\DepositController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductEntryController;
use App\Http\Controllers\ProductExitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAlertController;
use Illuminate\Support\Facades\Route;

Route::get('products', [ProductController::class, 'apiIndex']);
Route::get('products/{product}/history', [ProductController::class, 'apiHistory'])->whereNumber('product');
Route::get('products/{code}/check-code', [ProductController::class, 'checkCode'])->where('code', '[A-Za-z0-9\-_.]+');

Route::get('entries', [ProductEntryController::class, 'apiIndex']);
Route::get('exits', [ProductExitController::class, 'apiIndex']);

Route::get('deposits', [DepositController::class, 'apiIndex']);

Route::get('reports/search', [ReportController::class, 'apiSearch']);

Route::get('stock-alerts', [StockAlertController::class, 'apiIndex']);
Route::patch('stock-alerts/{stock_alert}/read', [StockAlertController::class, 'markRead']);
