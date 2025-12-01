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
        Schema::create('products', function (Blueprint $table) {

            $table->id();
            $table->string('name')->index();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('trade_offer_min_qty')->nullable();
            $table->integer('trade_offer_get_qty')->nullable();
            $table->decimal('discount', 5, 2)->nullable()->comment('Percentage discount');
            $table->dateTime('discount_or_trade_offer_start_date')->nullable();
            $table->dateTime('discount_or_trade_offer_end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('price');
            $table->index('stock');
            $table->index('discount_or_trade_offer_start_date');
            $table->index('discount_or_trade_offer_end_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
