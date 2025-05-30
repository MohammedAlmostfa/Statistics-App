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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->integer('type');
            $table->float('total_amount')->nullable();
            $table->float('discount_amount')->nullable();
            $table->float('paid_amount')->nullable();
            $table->float('sum_amount');
            $table->string('description')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index('agent_id');
            $table->index('transaction_date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
