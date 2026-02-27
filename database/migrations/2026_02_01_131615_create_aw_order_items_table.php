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
        Schema::create('aw_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            
            $table->string('product_name');
            $table->string('sku', 100);
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->boolean('is_bundle_parent')->default(false);
            $table->unsignedBigInteger('parent_item_id')->nullable();
            $table->boolean('is_free_gift')->default(false);
            $table->unsignedBigInteger('promotion_id')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_order_items');
    }
};
