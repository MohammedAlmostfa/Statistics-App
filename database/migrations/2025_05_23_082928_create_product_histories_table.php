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
        Schema::create('product_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->float('selling_price', 10, 2);
            $table->float('dollar_buying_price', 10, 2);
            $table->integer('dollar_exchange');
            $table->integer('installment_price');
            $table->integer('quantity');
            $table->timestamps();
            $table->index([ 'product_id']);

        });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_histories');
    }
};
