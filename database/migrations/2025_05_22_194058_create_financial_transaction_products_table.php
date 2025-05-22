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

            $table->foreignId('financial_transactions_id')->constrained('financial_transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('selling_price', 10, 2);
            $table->decimal('dollar_buying_price', 10, 2);
            $table->integer('dollar_exchange');
            $table->integer('quantity');
            $table->timestamps();
            $table->index(['financial_transactions_id', 'product_id'], 'financial_txn_product_idx');
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
