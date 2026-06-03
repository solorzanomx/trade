<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('trades', TradeController::class);

    Route::get('/metrics', [MetricsController::class, 'index'])->name('metrics.index');

    Route::get('/news', [NewsController::class, 'index'])->name('news.index');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');

    // Portafolio largo plazo
    Route::resource('portfolio', PortfolioController::class)->except(['show']);
    Route::post('/portfolio/{portfolioPosition}/price', [PortfolioController::class, 'updatePrice'])->name('portfolio.updatePrice');
});
