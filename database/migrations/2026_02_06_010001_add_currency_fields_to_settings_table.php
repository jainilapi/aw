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
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('base_currency_id')->nullable()->after('favicon');

            // Foreign key constraint - can be null for existing rows
            $table->foreign('base_currency_id')
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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['base_currency_id']);
            $table->dropColumn('base_currency_id');
        });
    }
};
