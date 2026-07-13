<?php

namespace App\Services;

class AspAnalysisService
{
    /**
     * Simulate an LLM call to analyze a single product based on product-analysis-schema.md
     */
    public function analyzeProduct(string $productName, array $userProfile, array $skinScores)
    {
        // In a real implementation, this would build a prompt using system-prompt.md
        // and call OpenAI/Anthropic APIs. For the hackathon, we return the exact
        // schema required by product-analysis-schema.md.

        return [
            "product" => [
                "brand" => "Mocked Brand",
                "product_name" => $productName,
                "category" => "serum",
                "variant" => null,
                "confidence" => 0.95,
                "needs_user_confirmation" => false
            ],
            "label_facts" => [
                "visible_ingredients" => ["Niacinamide", "Zinc PCA", "Water"],
                "claims_on_label" => ["oil-free", "brightening"],
                "spf_value" => null
            ],
            "ingredient_facts" => [
                "ingredients" => [
                    [
                        "input" => "Niacinamide",
                        "canonical_name" => "Niacinamide",
                        "functions" => ["brightening", "barrier-support"],
                        "flags" => [],
                        "matched" => true
                    ]
                ],
                "hero_ingredients" => ["Niacinamide", "Zinc PCA"],
                "has_fragrance" => false,
                "has_alcohol" => false,
                "pregnancy_safe" => true
            ],
            "skinsaver_opinion" => [
                "summary" => "A great serum for controlling oil and brightening skin.",
                "routine_slot" => "serum",
                "benefit_tags" => ["brightening", "oil-control"],
                "conflicts" => [], // Empty means no conflict
                "pros" => ["Controls sebum", "Fades dark spots", "Affordable"],
                "cons" => ["Can be drying if overused"],
                "decision" => "buy",
                "reason" => "Matches your oily skin profile perfectly without conflicts.",
                "fit_score" => 92,
                "cheaper_alternative_query" => "cheap niacinamide serum",
                "medical_claim_guardrail_ok" => true
            ]
        ];
    }

    /**
     * Simulate an LLM call to audit an entire wishlist based on api.md schema
     */
    public function auditWishlist(array $products, array $userProfile, array $skinScores, string $budget)
    {
        // Mocked response aligning exactly with api.md > POST /asp/audit-wishlist
        
        $buy = [];
        $wait = [];
        $skip = [];
        $replace = [];
        
        // Distribute the requested products into categories
        foreach ($products as $i => $product) {
            if ($i === 0) {
                $buy[] = ["product" => $product, "reason" => "Essential gap filled for hydration."];
            } elseif ($i === 1) {
                $wait[] = ["product" => $product, "reason" => "Good but not urgent, prioritize basics first."];
            } else {
                $skip[] = ["product" => $product, "reason" => "Conflicts with your current sensitive skin condition."];
            }
        }
        
        // If wishlist is empty, provide defaults
        if (empty($products)) {
            $buy[] = ["product" => "Beauty of Joseon Sunscreen", "reason" => "Essential daily protection gap."];
            $wait[] = ["product" => "COSRX Snail Mucin", "reason" => "Good for texture but not urgent."];
        }

        return [
            "buy" => $buy,
            "wait" => $wait,
            "skip" => $skip,
            "replace" => $replace,
            "scores" => [
                "wishlist_fit" => rand(60, 95),
                "budget_efficiency" => rand(50, 90),
                "duplicate_risk" => "low",
                "irritation_risk" => "low"
            ],
            "estimated_savings" => "Rp100.000 - Rp250.000"
        ];
    }

    /**
     * Simulate an LLM call to generate a premium AM/PM routine and alternative products
     */
    public function buildPremiumRoutine(array $profile, array $skinScores, array $wishlist)
    {
        // Mocked response aligning with price-rules.md, alternative-products.md, and frontend phase 7
        return [
            "routine" => [
                "am" => [
                    ["step" => 1, "type" => "Cleanser", "product" => "CeraVe Hydrating Cleanser", "price" => "$11.49", "usage" => "Daily"],
                    ["step" => 2, "type" => "Serum", "product" => "The Ordinary Niacinamide 10% + Zinc 1%", "price" => "$6.50", "usage" => "Daily"],
                    ["step" => 3, "type" => "Moisturizer", "product" => "Neutrogena Hydro Boost Gel Cream", "price" => "$14.99", "usage" => "Daily"],
                    ["step" => 4, "type" => "Sunscreen", "product" => "Beauty of Joseon Relief Sun SPF 50+", "price" => "$13.00", "usage" => "Daily"]
                ],
                "pm" => [
                    ["step" => 1, "type" => "Cleanser", "product" => "CeraVe Hydrating Cleanser", "price" => "$11.49", "usage" => "Daily"],
                    ["step" => 2, "type" => "Treatment", "product" => "The Ordinary Granactive Retinoid 2%", "price" => "$8.90", "usage" => "2-3x / week"],
                    ["step" => 3, "type" => "Moisturizer", "product" => "Neutrogena Hydro Boost Gel Cream", "price" => "$14.99", "usage" => "Daily"]
                ]
            ],
            "alternatives" => [
                [
                    "original" => ["name" => "Paula's Choice 2% BHA", "price" => "$34.00"],
                    "alternative" => ["name" => "COSRX BHA Blackhead Power Liquid", "price" => "$16.00", "fit" => 92, "saving" => "$18.00"],
                    "reason" => "Same active ingredient (BHA) at effective concentration, gentler formulation."
                ],
                [
                    "original" => ["name" => "Tatcha The Water Cream", "price" => "$72.00"],
                    "alternative" => ["name" => "Neutrogena Hydro Boost", "price" => "$14.99", "fit" => 88, "saving" => "$57.01"],
                    "reason" => "Provides similar gel-based hydration without pore-clogging heavy oils."
                ]
            ],
            "budget_summary" => [
                "total_cost" => "$45.88",
                "efficiency_score" => 92
            ],
            "medical_disclaimer" => "This report is generated by AI based on cosmetic ingredient databases. It is not medical advice. Patch test all new products before full application."
        ];
    }
}
