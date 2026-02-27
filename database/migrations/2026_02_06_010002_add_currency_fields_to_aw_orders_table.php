<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration adds multi-currency support to orders.
     * Existing orders will have null values for currency fields,
     * which means they were placed in base currency (USD by default).
     */
    public function up(): void
    {
        Schema::table('aw_orders', function (Blueprint $table) {
            // Rename existing 'currency' to 'currency_code' for clarity
            // This preserves existing data
            $table->renameColumn('currency', 'currency_code');
        });

        Schema::table('aw_orders', function (Blueprint $table) {
            // Add foreign key reference to currencies table
            $table->unsignedBigInteger('currency_id')
                ->nullable()
                ->after('currency_code');

            // Snapshot of exchange rate at the time of order
            // This protects historical orders from rate changes
            $table->decimal('exchange_rate_at_order', 12, 6)
                ->nullable()
                ->after('currency_id');

            // Store converted amounts for customer's currency display
            // Base currency amounts remain in existing columns
            $table->decimal('converted_sub_total', 12, 2)
                ->nullable()
                ->after('exchange_rate_at_order');

            $table->decimal('converted_grand_total', 12, 2)
                ->nullable()
                ->after('converted_sub_total');

            // Foreign key constraint
            $table->foreign('currency_id')
                ->references('id')
                ->on('aw_currencies')
                ->onDelete('set null');

            // Index for currency-based reporting
            $table->index('currency_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aw_orders', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropIndex(['currency_id']);
            $table->dropColumn([
                'currency_id',
                'exchange_rate_at_order',
                'converted_sub_total',
                'converted_grand_total'
            ]);
        });

        Schema::table('aw_orders', function (Blueprint $table) {
            $table->renameColumn('currency_code', 'currency');
        });
    }
};
