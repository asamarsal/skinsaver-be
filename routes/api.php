<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\AspController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ASP Registration Metadata (Public)
Route::get('/asp/metadata', [AspController::class, 'metadata']);

// Auth Endpoints (OKX Wallet Challenge-Response)
Route::post('/auth/challenge', [AuthController::class, 'challenge']);
Route::post('/auth/verify', [AuthController::class, 'verify']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // ZK-Privacy Scan Endpoints
    Route::post('/scan/selfie', [ScanController::class, 'uploadSelfie']);

    // Phase 8: Ingredient OCR Scanner
    Route::post('/asp/scan-ingredients', [AspController::class, 'scanIngredients']);

    // Core ASP Services
    Route::post('/asp/audit-product', [AspController::class, 'auditProduct']);
    Route::post('/asp/audit-wishlist', [AspController::class, 'auditWishlist']);

    // Premium ASP Services (Protected by x402 Payment Middleware)
    Route::post('/asp/generate-premium-report', [AspController::class, 'generatePremiumReport'])
        ->middleware('x402.verify');
});
