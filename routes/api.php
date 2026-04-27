<?php

use App\Http\Controllers\Api\TradeApiController;
use App\Http\Controllers\Api\MetricsApiController;
use App\Http\Controllers\Api\NewsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Trades
    Route::apiResource('trades', TradeApiController::class);
    Route::post('/trades/{trade}/comments', [TradeApiController::class, 'addComment']);

    // Assets
    Route::get('/assets', [TradeApiController::class, 'listAssets']);
    Route::post('/assets', [TradeApiController::class, 'storeAsset']);
    Route::delete('/assets/{asset}', [TradeApiController::class, 'destroyAsset']);

    // Metrics
    Route::get('/metrics/daily', [MetricsApiController::class, 'daily']);
    Route::get('/metrics/consistency', [MetricsApiController::class, 'consistency']);
    Route::get('/metrics/summary', [MetricsApiController::class, 'summary']);

    // News
    Route::get('/news/summaries', [NewsApiController::class, 'index']);
    Route::post('/news/generate', [NewsApiController::class, 'generate']);
});
