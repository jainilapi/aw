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
        Schema::create('aw_variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('attribute_value_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_variant_attribute_values');
    }
};
