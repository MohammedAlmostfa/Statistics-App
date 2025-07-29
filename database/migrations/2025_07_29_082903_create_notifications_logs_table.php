<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('notification_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->text('message');
        $table->boolean('status')->default(false);
        $table->text('response')->nullable();
        $table->timestamps();

        $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
    });
}


    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
