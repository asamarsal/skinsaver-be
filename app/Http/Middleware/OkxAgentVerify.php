<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OkxAgentVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $agentSig = $request->header('X-OKX-Agent-Signature');
        $agentId  = $request->header('X-OKX-Agent-ID');
        
        // Skip verification if we are in DEMO_MODE for the hackathon
        if (config('app.env') === 'local' && env('DEMO_MODE') === true) {
            return $next($request);
        }

        // Verify against OKX.AI public key
        // Reference: https://docs.okx.com/web3/build/asp/authentication
        if (!$agentSig || !$this->verifyOkxSignature($agentSig, $agentId, $request)) {
            return response()->json(['error' => 'Unauthorized agent'], 401);
        }
        
        return $next($request);
    }

    private function verifyOkxSignature(?string $signature, ?string $agentId, Request $request): bool
    {
        // Mock implementation for hackathon if not skipped by DEMO_MODE
        // In production, this would verify the signature using OKX's public key
        return strlen((string)$signature) > 10 && $agentId !== null;
    }
}
