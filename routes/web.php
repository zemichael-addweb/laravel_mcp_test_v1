<?php

use App\Http\Controllers\McpController;
use Illuminate\Support\Facades\Route;

Route::get('/', [McpController::class, 'index'])->name('mcp.index');

Route::prefix('mcp')->name('mcp.')->group(function () {
    Route::get('/', [McpController::class, 'index'])->name('index');
    Route::post('/search', [McpController::class, 'search'])->name('search');
    Route::get('/requests', [McpController::class, 'requests'])->name('requests');
    Route::get('/requests/{id}', [McpController::class, 'requestDetails'])->name('request.details');
});

Route::prefix('api')->name('api.')->group(function () {
    Route::post('/search', [McpController::class, 'apiSearch'])->name('search');
    Route::get('/recent-requests', [McpController::class, 'recentRequests'])->name('recent.requests');
});
