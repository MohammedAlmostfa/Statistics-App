<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('financial_transactions_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_id')->constrained('financial_transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('dollar_exchange');
            $table->float('selling_price', 10, 2);
            $table->float('dolar_buying_price', 10, 2);
            $table->integer('installment_price');
            $table->integer('quantity');
            $table->timestamps();
            $table->index(['financial_id', 'product_id'], 'financial_txn_product_idx');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
