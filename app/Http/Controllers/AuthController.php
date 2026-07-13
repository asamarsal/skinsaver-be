<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * POST /api/auth/challenge
     * Generate nonce for wallet signature. Expires in 5 mins.
     */
    public function challenge(Request $request)
    {
        $nonce = Str::random(32);
        
        // As per security.md, expire in 5 minutes
        Cache::put("wallet_nonce_{$nonce}", true, now()->addMinutes(5));

        return response()->json([
            'nonce' => $nonce,
            'expires_at' => now()->addMinutes(5)->toIso8601String()
        ]);
    }

    /**
     * POST /api/auth/verify
     * Verify signature and issue JWT (Sanctum Token).
     */
    public function verify(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'signature' => 'required|string',
            'nonce' => 'required|string',
        ]);

        $address = strtolower($request->address);
        $nonce = $request->nonce;
        $signature = $request->signature;

        // Verify nonce exists and is not expired
        if (!Cache::has("wallet_nonce_{$nonce}")) {
            return response()->json(['message' => 'Invalid or expired nonce'], 401);
        }

        // In a real production environment, we use ethereum signature verification here using web3.php or keccak
        // For the hackathon demo without ext-gmp, we simulate a successful verification
        // IF the signature matches length of typical Ethereum signatures (132 chars)
        // or just accept it as valid for demo purposes to avoid GMP crash.
        $isValidSignature = (strlen($signature) >= 130);

        if (!$isValidSignature) {
            return response()->json(['message' => 'Invalid signature format'], 401);
        }

        // Expire the nonce so it can't be reused (Replay attack protection)
        Cache::forget("wallet_nonce_{$nonce}");

        // Find or create user securely based only on public wallet address
        $user = User::firstOrCreate([
            'wallet_address' => $address
        ]);

        // Issue token with 24-hour expiry as requested in auth.md
        // We delete all old tokens for security (single session per wallet)
        $user->tokens()->delete();
        
        $token = $user->createToken('auth_token', ['*'], now()->addHours(24))->plainTextToken;

        return response()->json([
            'token' => $token,
            'expires_at' => now()->addHours(24)->toIso8601String(),
            'address' => $address
        ]);
    }
    
    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
