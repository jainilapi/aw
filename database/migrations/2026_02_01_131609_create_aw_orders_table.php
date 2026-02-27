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
        Schema::create('aw_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->unsignedBigInteger('customer_id')->index();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'rejected', 'returned'])->default('pending')->index();
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid');
            $table->boolean('is_b2b')->default(false);

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_notes')->nullable();

            $table->string('shipping_address_line_1');
            $table->string('shipping_address_line_2')->nullable();
            $table->unsignedBigInteger('shipping_country_id');
            $table->unsignedBigInteger('shipping_state_id');
            $table->unsignedBigInteger('shipping_city_id');
            $table->string('shipping_zipcode', 20);
            $table->string('recipient_name');
            $table->string('recipient_contact_number', 20);
            $table->string('recipient_email')->nullable();

            $table->string('billing_address_line_1');
            $table->string('billing_address_line_2')->nullable();
            $table->unsignedBigInteger('billing_country_id');
            $table->unsignedBigInteger('billing_state_id');
            $table->unsignedBigInteger('billing_city_id');
            $table->string('billing_zipcode', 20);
            $table->string('billing_name');
            $table->string('billing_contact_number', 20);
            $table->string('billing_email')->nullable();

            $table->string('shipping_provider', 100)->nullable();
            $table->string('tracking_number', 100)->nullable();

            $table->decimal('sub_total', 12, 2);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('credit_utilization', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_due', 12, 2);
            $table->string('payment_method', 50)->default('cash_on_delivery');

            $table->string('currency', 10)->default('USD');
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_orders');
    }
};
