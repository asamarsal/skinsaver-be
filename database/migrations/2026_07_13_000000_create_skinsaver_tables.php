<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beauty_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('skin_type');
            $table->jsonb('concerns')->nullable();
            $table->jsonb('sensitivities')->nullable();
            $table->string('budget_tier');
            $table->timestamps();
        });

        Schema::create('skin_scans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('profile_id')->nullable()->constrained('beauty_profiles')->onDelete('set null');
            $table->jsonb('visual_notes')->nullable();
            $table->jsonb('skin_scores')->nullable();
            $table->string('image_hash')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('brand');
            $table->string('name');
            $table->string('category');
            $table->jsonb('inci_list')->nullable();
            $table->timestamps();
        });

        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->string('canonical_name');
            $table->jsonb('functions')->nullable();
            $table->jsonb('flags')->nullable();
            $table->timestamps();
        });

        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('audit_type'); // e.g., 'single', 'wishlist'
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('audit_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained('audits')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->string('decision'); // buy, skip, wait, replace
            $table->jsonb('scores')->nullable();
            $table->timestamps();
        });

        Schema::create('routines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained('audits')->onDelete('cascade');
            $table->jsonb('morning_steps')->nullable();
            $table->jsonb('night_steps')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('audit_id')->nullable()->constrained('audits')->onDelete('set null');
            $table->decimal('amount', 10, 4);
            $table->string('tx_hash')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('routines');
        Schema::dropIfExists('audit_results');
        Schema::dropIfExists('audits');
        Schema::dropIfExists('product_ingredients');
        Schema::dropIfExists('products');
        Schema::dropIfExists('skin_scans');
        Schema::dropIfExists('beauty_profiles');
    }
};
