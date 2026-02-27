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
        Schema::create('aw_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_id')->nullable();
            $table->integer('min_qty');
            $table->integer('max_qty')->nullable();
            $table->decimal('price', 12, 2);
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index('price_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_price_tiers');
    }
};
