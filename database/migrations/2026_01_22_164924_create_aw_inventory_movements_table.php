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
        Schema::create('aw_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedBigInteger('unit_id')->constrained();
            $table->unsignedBigInteger('warehouse_id')->constrained();
            $table->integer('quantity_change');
            $table->enum('reason', ['purchase', 'sale', 'return', 'adjustment', 'transfer']);
            $table->string('reference')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['product_id', 'variant_id']);
            $table->index('warehouse_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_inventory_movements');
    }
};
