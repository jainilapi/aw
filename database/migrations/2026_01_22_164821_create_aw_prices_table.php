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
        Schema::create('aw_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('original_unit_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->enum('pricing_type', ['fixed', 'tiered']);
            $table->decimal('base_price', 12, 2);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['product_id', 'variant_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_prices');
    }
};
