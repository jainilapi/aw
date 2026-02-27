<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aw_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);                          // e.g., "US Dollar", "Euro"
            $table->string('iso_code', 3)->unique();              // ISO 4217 code: USD, EUR, INR
            $table->string('symbol', 10);                         // $, €, ₹
            $table->decimal('exchange_rate', 12, 6)->default(1);  // Rate relative to base currency
            $table->boolean('is_base')->default(false)->index();  // Only one currency can be base
            $table->boolean('is_active')->default(true)->index(); // Available for frontend selection
            $table->tinyInteger('decimal_places')->default(2);    // Formatting precision
            $table->enum('symbol_position', ['before', 'after'])->default('before');
            $table->unsignedInteger('sort_order')->default(0);    // Display order
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']); // Composite index for frontend queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_currencies');
    }
};
