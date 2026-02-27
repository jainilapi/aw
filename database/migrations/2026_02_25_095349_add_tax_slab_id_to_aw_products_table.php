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
        Schema::table('aw_products', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_slab_id')->nullable()->after('brand_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aw_products', function (Blueprint $table) {
            $table->dropColumn('tax_slab_id');
        });
    }
};
