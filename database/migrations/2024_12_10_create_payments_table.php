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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id');
            $table->string('transaction_id')->unique();
            $table->string('payment_method');
            $table->string('cardholder_name');
            $table->string('card_last_four');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('billing_address');
            $table->string('city');
            $table->string('zip_code');
            $table->string('country');
            $table->string('user_email')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('booking_id')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
