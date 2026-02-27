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
        Schema::create('product_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file')->nullable();
            $table->tinyInteger('type')->default(0)->comment('0 = Product Excel | 1 = Product Image');
            $table->tinyInteger('override')->default(0)->comment('0 = No override Image | 1 = Override Image');
            $table->unsignedBigInteger('imported_by')->nullable();
            $table->enum('status', ['pending', 'in-queue', 'imported', 'failed'])->default('pending');
            $table->integer('total_rows')->default(0);
            $table->string('error_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_imports');
    }
};
