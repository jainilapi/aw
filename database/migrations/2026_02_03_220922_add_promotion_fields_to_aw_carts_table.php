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
        Schema::table('aw_carts', function (Blueprint $table) {
            $table->unsignedBigInteger('applied_promotion_id')->nullable();
            $table->string('applied_promotion_code', 100)->nullable()->after('applied_promotion_id');

            $table->foreign('applied_promotion_id')->references('id')->on('aw_promotions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aw_carts', function (Blueprint $table) {
            $table->dropForeign(['applied_promotion_id']);
            $table->dropColumn(['applied_promotion_id', 'applied_promotion_code']);
        });
    }
};
