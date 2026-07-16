<?php

namespace App\Http\Controllers;

use App\Services\AspAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class McpController extends Controller
{
    public function __construct(private AspAnalysisService $analysisService) {}

    public function handleTask(Request $request): JsonResponse
    {
        $request->validate([
            'task_id'    => 'required|string',
            'service_id' => 'required|string',
            'input'      => 'required|array',
        ]);

        $serviceId = $request->service_id;
        $input     = $request->input;
        $user      = $request->user();
        
        // Mock user profile if not logged in since it might be called from OKX Agent
        $profile   = $user?->beautyProfile?->toArray() ?? [];

        return match($serviceId) {
            'wishlist_audit' => response()->json([
                'task_id'   => $request->task_id,
                'status'    => 'completed',
                'timestamp' => now()->toIso8601String(),
                'result'    => $this->analysisService->auditWishlist(
                    $input['products'] ?? [],
                    $profile,
                    $input['skin_scores'] ?? [],
                    $input['budget'] ?? 'medium'
                ),
            ]),
            'premium_report' => response()->json([
                'task_id'   => $request->task_id,
                'status'    => 'completed',
                'timestamp' => now()->toIso8601String(),
                'result'    => $this->analysisService->buildPremiumRoutine(
                    $profile, 
                    $input['skin_scores'] ?? [], 
                    $input['wishlist'] ?? []
                ),
            ]),
            'skin_analysis' => response()->json([
                'task_id'   => $request->task_id,
                'status'    => 'completed',
                'timestamp' => now()->toIso8601String(),
                'result'    => $this->analysisService->analyzeProduct(
                    $input['product_name'] ?? 'Unknown',
                    $profile,
                    $input['skin_scores'] ?? []
                ),
            ]),
            default => response()->json(['error' => 'Unknown service_id'], 404),
        };
    }

    public function capabilities(): JsonResponse
    {
        return response()->json([
            'services' => [
                'wishlist_audit',
                'premium_report',
                'skin_analysis',
            ]
        ]);
    }
}
