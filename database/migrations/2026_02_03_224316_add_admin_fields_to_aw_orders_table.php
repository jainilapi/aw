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
            $table->unsignedBigInteger('created_by')->nullable()->after('customer_id');
            $table->enum('source', ['customer', 'admin'])->default('customer')->after('created_by');

            $table->index('source');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aw_orders', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropIndex(['created_by']);
            $table->dropColumn(['created_by', 'source']);
        });
    }
};
