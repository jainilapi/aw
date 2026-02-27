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
        Schema::create('aw_supplier_warehouse_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->integer('quantity');
            $table->integer('reorder_level')->default(0)->nullable();
            $table->integer('max_stock')->default(0)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();

            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['product_id', 'variant_id']);
            $table->index(['supplier_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_supplier_warehouse_products');
    }
};
