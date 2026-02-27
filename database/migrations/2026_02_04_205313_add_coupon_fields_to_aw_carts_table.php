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
            $table->unsignedBigInteger('applied_coupon_id')->nullable()->after('user_id');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('applied_coupon_id');
            $table->string('coupon_code')->nullable()->after('discount_amount');

            $table->foreign('applied_coupon_id')
                ->references('id')
                ->on('aw_promotions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aw_carts', function (Blueprint $table) {
            $table->dropForeign(['applied_coupon_id']);
            $table->dropColumn(['applied_coupon_id', 'discount_amount', 'coupon_code']);
        });
    }
};
