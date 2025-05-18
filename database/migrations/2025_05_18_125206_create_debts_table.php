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
            $table->integer('receipt_number');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->integer('total_debt', 10, 2);
            $table->integer('remaining_debt', 10, 2);
            $table->date('debt_date');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('debts');
    }
}
