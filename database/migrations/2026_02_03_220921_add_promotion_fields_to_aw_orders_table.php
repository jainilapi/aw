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
        Schema::table('aw_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id')->nullable()->after('discount_total');
            $table->string('promotion_code', 100)->nullable()->after('promotion_id');
            $table->enum('promotion_type', ['catdisc', 'prodisc', 'cardisc', 'buyxgetx'])->nullable()->after('promotion_code');
            $table->double('promotion_discount')->default(0)->after('promotion_type');

            $table->foreign('promotion_id')->references('id')->on('aw_promotions')->onDelete('set null');
            $table->index('promotion_id');
        });

        Schema::table('aw_order_items', function (Blueprint $table) {
            $table->boolean('is_free_item')->default(false)->after('total')->comment('For buyxgetx free items');
            $table->unsignedBigInteger('free_from_promotion_id')->nullable()->after('is_free_item');

            $table->foreign('free_from_promotion_id')->references('id')->on('aw_promotions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aw_order_items', function (Blueprint $table) {
            $table->dropForeign(['free_from_promotion_id']);
            $table->dropColumn(['is_free_item', 'free_from_promotion_id']);
        });

        Schema::table('aw_orders', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropIndex(['promotion_id']);
            $table->dropColumn(['promotion_id', 'promotion_code', 'promotion_type', 'promotion_discount']);
        });
    }
};
