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
        Schema::create('aw_product_units', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();

            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('parent_unit_id')->nullable();

            $table->decimal('conversion_factor', 12, 4);
            $table->double('quantity')->default(0);
            $table->boolean('is_base')->default(false);
            $table->boolean('is_default_selling')->default(false);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['product_id', 'variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_product_units');
    }
};
