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
        Schema::create('aw_products', function (Blueprint $table) {
            $table->id();
            $table->enum('product_type', ['simple', 'variable', 'bundle'])->index();
            $table->string('name');
            $table->string('sku')->nullable()->comment('SKU for product_type = simple');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('slug')->unique();
            $table->foreignId('brand_id')->nullable()->index();
            $table->string('short_description', 500)->nullable();
            $table->text('long_description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('inactive')->index();
            $table->boolean('is_b2b')->default(false);
            $table->boolean('in_stock')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_products');
    }
};
