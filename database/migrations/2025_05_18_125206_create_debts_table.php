<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebtsTable extends Migration
{
    public function up()
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedInteger('payment_amount');
            $table->unsignedInteger('remaining_debt');
            $table->date('debt_date');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text("description")->nullable();
            $table->timestamps();
            $table->index([ 'debt_date']);

        });
    }

    public function down()
    {
        Schema::dropIfExists('debts');
    }
}
