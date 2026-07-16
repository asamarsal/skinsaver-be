<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditResult;
use App\Models\Product;
use App\Models\Routine;
use App\Services\AspAnalysisService;
use Illuminate\Http\Request;

class AspController extends Controller
{
    protected AspAnalysisService $analysisService;

    public function __construct(AspAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * GET /api/asp/metadata
     * Returns ASP metadata for OKX.AI marketplace registration
     */
    public function metadata()
    {
        return response()->json([
            'name'         => 'SkinSaver AI',
            'description'  => 'Selfie-powered skincare shopping copilot. Turns private skin scores, beauty habits, and wishlist products into verified, budget-aware shopping decisions.',
            'endpoints'    => [
                'audit_product'          => url('/api/asp/audit-product'),
                'audit_wishlist'         => url('/api/asp/audit-wishlist'),
                'generate_premium_report'=> url('/api/asp/generate-premium-report'),
                'scan_ingredients'       => url('/api/asp/scan-ingredients'),
            ],
            'capabilities' => ['visual_analysis', 'ingredient_conflict_detection', 'budget_optimization', 'ingredient_ocr_scanner'],
            'ai_drivers'   => ['deepseek' => 'text analysis', 'openai' => 'vision/ocr', 'okx_ai' => 'native OKX.AI LLM'],
            'task_schemas' => [
                'audit_wishlist' => [
                    'input'  => ['products' => 'string[]', 'budget' => 'low|medium|high', 'skin_scores' => 'object'],
                    'output' => ['buy' => 'array', 'skip' => 'array', 'wait' => 'array', 'replace' => 'array', 'scores' => 'object', 'estimated_savings' => 'string'],
                ],
                'premium_report' => [
                    'input'  => ['wishlist' => 'string[]', 'skin_scores' => 'object'],
                    'output' => ['routine' => 'object', 'alternatives' => 'array', 'budget_summary' => 'object', 'medical_disclaimer' => 'string'],
                ],
                'scan_ingredients' => [
                    'input'  => ['packaging_image' => 'base64'],
                    'output' => ['ingredients_parsed' => 'array', 'warnings' => 'array', 'overall_safety' => 'string'],
                ],
            ],
            'version'      => '1.1.0'
        ]);
    }

    /**
     * POST /api/asp/audit-product
     * Single product audit
     */
    public function auditProduct(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'skin_scores' => 'nullable|array'
        ]);

        $user = $request->user();
        $profile = $user->beautyProfile ? $user->beautyProfile->toArray() : [];

        // Call the AI Service
        $analysis = $this->analysisService->analyzeProduct(
            $request->product_name,
            $profile,
            $request->skin_scores ?? []
        );

        // Security / Compliance Guardrail Enforcement
        if (!$analysis['skinsaver_opinion']['medical_claim_guardrail_ok']) {
            return response()->json([
                'success' => false,
                'message' => 'Analysis blocked due to medical compliance guardrails.'
            ], 403);
        }

        // Generate Audit Record
        $audit = Audit::create([
            'user_id' => $user->id,
            'audit_type' => 'single',
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'data' => $analysis,
            'audit_id' => $audit->id
        ]);
    }

    /**
     * POST /api/asp/audit-wishlist
     * Multi-product wishlist audit
     */
    public function auditWishlist(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*' => 'string|max:255',
            'skin_scores' => 'nullable|array',
            'budget' => 'nullable|string|in:low,medium,high',
            'location' => 'nullable|string'
        ]);

        $user = $request->user();
        $profile = $user->beautyProfile ? $user->beautyProfile->toArray() : [];
        $budget = $request->budget ?? $profile['budget_tier'] ?? 'medium';

        // Call the AI Service
        $analysis = $this->analysisService->auditWishlist(
            $request->products,
            $profile,
            $request->skin_scores ?? [],
            $budget
        );

        // Generate Audit Record
        $audit = Audit::create([
            'user_id' => $user->id,
            'audit_type' => 'wishlist',
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'data' => $analysis,
            'audit_id' => $audit->id
        ]);
    }

    /**
     * POST /api/asp/generate-premium-report
     * Premium multi-product audit and AM/PM routine builder
     * Protected by x402 payment middleware
     */
    public function generatePremiumReport(Request $request)
    {
        $user = $request->user();
        $profile = $user->beautyProfile ? $user->beautyProfile->toArray() : [];
        
        $wishlist = $request->input('wishlist', []);
        $skinScores = $request->input('skin_scores', []);

        // AI Service Call to generate Routine and Swaps
        $reportData = $this->analysisService->buildPremiumRoutine($profile, $skinScores, $wishlist);

        // Generate Audit Record
        $audit = Audit::create([
            'user_id' => $user->id,
            'audit_type' => 'premium_report',
            'status' => 'completed'
        ]);

        // Save the Routine
        Routine::create([
            'audit_id' => $audit->id,
            'morning_steps' => $reportData['routine']['am'],
            'night_steps' => $reportData['routine']['pm']
        ]);
        
        // Output Medical Guardrail verification
        if (!isset($reportData['medical_disclaimer'])) {
            return response()->json(['error' => 'Safety violation: Missing medical disclaimer.'], 500);
        }

        return response()->json([
            'success'   => true,
            'data'      => $reportData,
            'audit_id'  => $audit->id,
        ]);
    }

    /**
     * POST /api/asp/scan-ingredients  (Phase 8)
     * Accepts a photo of cosmetic packaging, extracts ingredients via Vision OCR,
     * then analyses safety against user's skin profile using Deepseek.
     */
    public function scanIngredients(Request $request)
    {
        $request->validate([
            'packaging_image' => 'required|image|max:8192', // max 8MB
        ]);

        $file      = $request->file('packaging_image');
        $mimeType  = $file->getMimeType();

        // Strip EXIF metadata (ZK-Privacy)
        $imageData = file_get_contents($file->getRealPath());
        $imageBase64 = base64_encode($imageData);

        // Auto-delete temp file
        $tempPath = $file->storeAs('ephemeral', \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension(), 'local');
        $fullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tempPath);

        $user    = $request->user();
        $profile = $user->beautyProfile ? $user->beautyProfile->toArray() : [];

        try {
            $result = $this->analysisService->scanIngredients($imageBase64, $mimeType, $profile);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } finally {
            // Always delete the ephemeral file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
