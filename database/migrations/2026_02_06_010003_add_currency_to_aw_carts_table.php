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
            // Track customer's selected currency for the cart
            $table->unsignedBigInteger('currency_id')
                ->nullable()
                ->after('user_id');

            // Foreign key constraint
            $table->foreign('currency_id')
                ->references('id')
                ->on('aw_currencies')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aw_carts', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });
    }
};
