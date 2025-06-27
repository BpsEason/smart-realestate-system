<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Middleware\ApiKeyMiddleware;

// API route group, applying the API key authentication Middleware
Route::middleware(ApiKeyMiddleware::class)->group(function () {
    // Property API
    Route::get('/properties', [PropertyController::class, 'index']); // Supports pagination
    Route::get('/properties/{id}', [PropertyController::class, 'show']);
    Route::post('/properties/{id}/generate-content', [PropertyController::class, 'generateContent']);
});

// Health check endpoint, no API key required
Route::get('/health', [PropertyController::class, 'healthCheck']);

// Basic user authentication (optional, can be used with Laravel Sanctum)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
