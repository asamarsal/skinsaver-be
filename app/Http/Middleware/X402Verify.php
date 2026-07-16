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

        // Skip verification if we are in DEMO_MODE for the hackathon UI preview
        // but still validate structure if header is present
        if (!$paymentHeader) {
            if (config('app.env') === 'local' && env('DEMO_MODE') === true) {
                return $next($request);
            }
            return response()->json([
                'error' => 'payment_required',
                'message' => 'This is a premium endpoint. Please authorize payment via OKX Wallet.',
                'accepts' => [[
                    'scheme' => 'exact',
                    'price'  => '5.00',
                    'token'  => 'USDT',
                    'network' => 'eip155:196',
                    'pay_to' => config('okx.vault_address', '0xMockSkinsaverVaultAddress')
                ]]
            ], 402);
        }

        $payment = json_decode($paymentHeader, true);
        if (!$payment || !isset($payment['signature'])) {
            return response()->json(['error' => 'invalid_payment_format'], 400);
        }

        // Validate expiration
        if ($payment['validBefore'] < time()) {
            return response()->json(['error' => 'payment_authorization_expired'], 400);
        }

        // Validate recipient address
        if (strtolower($payment['to']) !== strtolower(config('okx.vault_address', '0xMockSkinsaverVaultAddress'))) {
            return response()->json(['error' => 'invalid_vault_address'], 400);
        }

        // Validate amount (5 USDT = 5000000)
        if ($payment['value'] !== '5000000') {
            return response()->json(['error' => 'invalid_amount'], 400);
        }

        // Check if nonce has already been used
        $exists = \Illuminate\Support\Facades\DB::table('payment_logs')->where('nonce', $payment['nonce'])->exists();
        if ($exists) {
            return response()->json(['error' => 'nonce_already_used'], 403);
        }

        // Verify the payment signature.
        // In production, the OKX x402 Broker signature (EIP-3009) would be verified here.
        // For the hackathon demo without ext-gmp or web3.php, we simulate verification.
        $signatureValid = strlen($payment['signature']) > 10;
        
        if (!$signatureValid) {
            return response()->json([
                'error' => 'invalid_payment_signature',
                'message' => 'The provided payment signature is invalid.'
            ], 403);
        }

        // Record the payment
        \Illuminate\Support\Facades\DB::table('payment_logs')->insert([
            'user_id'      => $request->user()?->id,
            'nonce'        => $payment['nonce'],
            'from_address' => $payment['from'],
            'to_address'   => $payment['to'],
            'amount_usdt'  => $payment['value'],
            'valid_before' => $payment['validBefore'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $request->attributes->set('x402_payment', $payment);

        return $next($request);
    }
}
