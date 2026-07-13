<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\BeautyProfile;
use App\Models\Product;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Curated Products (From seed-data.md)
        $products = [
            [
                'brand' => 'Paula\'s Choice',
                'name' => 'Skin Perfecting 2% BHA Liquid Exfoliant',
                'category' => 'Toner/Exfoliant',
                'inci_list' => ['Water', 'Methylpropanediol', 'Butylene Glycol', 'Salicylic Acid', 'Polysorbate 20', 'Camellia Oleifera Leaf Extract']
            ],
            [
                'brand' => 'COSRX',
                'name' => 'Advanced Snail 92 All in One Cream',
                'category' => 'Moisturizer',
                'inci_list' => ['Snail Secretion Filtrate', 'Betaine', 'Caprylic/Capric Triglyceride', 'Cetearyl Olivate', 'Sorbitan Olivate']
            ],
            [
                'brand' => 'Rohto Mentholatum',
                'name' => 'Melano CC Vitamin C Essence',
                'category' => 'Serum',
                'inci_list' => ['Ascorbic Acid', 'Tocopheryl Acetate', 'Dipotassium Glycyrrhizate', 'O-Cymen-5-Ol', 'Ascorbyl Tetraisopalmitate']
            ],
            [
                'brand' => 'The Ordinary',
                'name' => 'Niacinamide 10% + Zinc 1%',
                'category' => 'Serum',
                'inci_list' => ['Aqua (Water)', 'Niacinamide', 'Pentylene Glycol', 'Zinc PCA', 'Dimethyl Isosorbide']
            ],
            [
                'brand' => 'Beauty of Joseon',
                'name' => 'Relief Sun : Rice + Probiotics (SPF50+ PA++++)',
                'category' => 'Sunscreen',
                'inci_list' => ['Water', 'Oryza Sativa (Rice) Extract', 'Dibutyl Adipate', 'Propanediol', 'Diethylamino Hydroxybenzoyl Hexyl Benzoate', 'Polymethylsilsesquioxane']
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['name' => $productData['name']],
                $productData
            );
        }

        // 2. Seed Beauty Profiles (Demo Users)
        
        // Profile A: Oily/Acne-Prone
        $userA = User::firstOrCreate(
            ['wallet_address' => '0xMockOilyUserAddress123'],
            ['auth_level' => 'user']
        );
        BeautyProfile::firstOrCreate(
            ['user_id' => $userA->id],
            [
                'skin_type' => 'Oily',
                'concerns' => ['Pores', 'Texture'],
                'sensitivities' => [],
                'budget_tier' => 'Medium'
            ]
        );

        // Profile B: Dry/Sensitive
        $userB = User::firstOrCreate(
            ['wallet_address' => '0xMockDryUserAddress456'],
            ['auth_level' => 'user']
        );
        BeautyProfile::firstOrCreate(
            ['user_id' => $userB->id],
            [
                'skin_type' => 'Dry',
                'concerns' => ['Redness', 'Dullness'],
                'sensitivities' => ['Fragrance', 'Alcohol'],
                'budget_tier' => 'Low'
            ]
        );
    }
}
