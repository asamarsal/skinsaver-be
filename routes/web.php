<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/.well-known/asp.json', function() {
    return response()->json([
        'asp_version'  => '1.0',
        'name'         => 'SkinSaver AI',
        'description'  => 'Selfie-powered skincare shopping copilot on X Layer',
        'logo_url'     => config('app.url') . '/logo.png',
        'base_url'     => config('app.url'),
        'auth_type'    => 'okx_wallet_challenge',
        'payment' => [
            'protocol'  => 'x402',
            'token'     => 'USDT',
            'network'   => 'eip155:196',
            'price_map' => [
                'premium_report'    => '5.00',
                'scan_ingredients'  => '1.00',
            ],
        ],
        'services' => [
            [
                'id'             => 'wishlist_audit',
                'name'           => 'Wishlist Audit',
                'endpoint'       => '/api/asp/audit-wishlist',
                'method'         => 'POST',
                'input_schema'   => ['products' => 'array', 'skin_scores' => 'object'],
                'output_schema'  => ['buy' => 'array', 'skip' => 'array', 'scores' => 'object'],
            ],
            [
                'id'       => 'premium_report',
                'name'     => 'Premium Full Audit',
                'endpoint' => '/api/asp/generate-premium-report',
                'method'   => 'POST',
                'payment'  => true,
                'price'    => '5.00 USDT',
            ],
            [
                'id'       => 'scan_ingredients',
                'name'     => 'Ingredient OCR Scanner',
                'endpoint' => '/api/asp/scan-ingredients',
                'method'   => 'POST',
                'payment'  => true,
                'price'    => '1.00 USDT',
            ],
        ],
    ])->header('Access-Control-Allow-Origin', '*');
});
