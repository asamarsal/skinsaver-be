<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class X402Verify
{
    /**
     * Handle an incoming request for x402 Payment Middleware
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $paymentHeader = $request->header('X-Payment');

        if (!$paymentHeader) {
            return response()->json([
                'error' => 'payment_required',
                'message' => 'This is a premium endpoint. Please authorize payment via OKX Wallet.',
                'accepts' => [[
                    'scheme' => 'exact',
                    'price'  => '5.00', // Standard premium price (USDT)
                    'token'  => 'USDT',
                    'network' => 'eip155:196', // X Layer Mainnet
                    'pay_to' => config('okx.pay_to_address', '0xMockSkinsaverVaultAddress')
                ]]
            ], 402);
        }

        // Verify the payment signature.
        // In production, the OKX x402 Broker signature (EIP-3009) would be verified here.
        // For the hackathon demo without ext-gmp, we simulate verification.
        if (strlen($paymentHeader) < 10) {
            return response()->json([
                'error' => 'invalid_payment_signature',
                'message' => 'The provided payment signature is invalid.'
            ], 403);
        }

        return $next($request);
    }
}
