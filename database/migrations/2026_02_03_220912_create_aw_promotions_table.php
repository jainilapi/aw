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
        Schema::create('aw_promotions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'catdisc',
                'prodisc',
                'cardisc',
                'buyxgetx'
            ])->default('catdisc')
                ->comment('
                catdisc = Discount on Selected Categories in Cart
                prodisc = Discount on Selected Products in Cart
                cardisc = Discount on Total amount in Cart
                buyxgetx = Buy X Get X'
                );
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->longText('how_to_use')->nullable();
            $table->longText('terms_and_condition')->nullable();
            $table->string('posters')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('auto_applicable')->default(0);
            $table->json('warehouse_id')->nullable();
            $table->json('category_id')->nullable();
            $table->json('product_id')->nullable();
            $table->json('variant_id')->nullable();
            $table->json('unit_id')->nullable();
            $table->boolean('discount_type')->default(0)->comment('0 = Percentage | 1 = Fixed');
            $table->double('discount_amount')->nullable();
            $table->double('cart_minimum_amount')->nullable();
            $table->unsignedBigInteger('x_product')->nullable()->comment('On Buy X Product Get Y Product Free');
            $table->unsignedBigInteger('x_variant')->nullable();
            $table->unsignedBigInteger('x_unit')->nullable();
            $table->integer('x_quantity')->nullable()->comment('Quantity to buy for Buy X Get Y');
            $table->unsignedBigInteger('y_item')->nullable()->comment('On Buy X Product Get Y Product Free');
            $table->unsignedBigInteger('y_variant')->nullable();
            $table->unsignedBigInteger('y_unit')->nullable();
            $table->integer('y_quantity')->nullable()->comment('Quantity to get free for Buy X Get Y');
            $table->integer('application_limit')->default(1)->comment('How many times user could use this coupon');
            $table->boolean('status')->default(0)->comment('0 = InActive | 1 = Active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aw_promotions');
    }
};
