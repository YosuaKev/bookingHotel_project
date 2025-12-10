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
        Schema::table('bookings', function (Blueprint $table) {
            // Check and add missing columns
            if (!Schema::hasColumn('bookings', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->first();
            }
            if (!Schema::hasColumn('bookings', 'booking_id')) {
                $table->string('booking_id')->unique();
            }
            if (!Schema::hasColumn('bookings', 'first_name')) {
                $table->string('first_name');
            }
            if (!Schema::hasColumn('bookings', 'last_name')) {
                $table->string('last_name');
            }
            if (!Schema::hasColumn('bookings', 'room_type')) {
                $table->string('room_type');
            }
            if (!Schema::hasColumn('bookings', 'check_in')) {
                $table->dateTime('check_in');
            }
            if (!Schema::hasColumn('bookings', 'check_out')) {
                $table->dateTime('check_out');
            }
            if (!Schema::hasColumn('bookings', 'guests')) {
                $table->integer('guests');
            }
            if (!Schema::hasColumn('bookings', 'nights')) {
                $table->integer('nights');
            }
            if (!Schema::hasColumn('bookings', 'rate')) {
                $table->decimal('rate', 10, 2);
            }
            if (!Schema::hasColumn('bookings', 'total')) {
                $table->decimal('total', 10, 2);
            }
            if (!Schema::hasColumn('bookings', 'special_requests')) {
                $table->text('special_requests')->nullable();
            }
        });

        // Add foreign key
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'user_id')) {
                try {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key may already exist
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
                // Foreign key may not exist
            }
        });
    }
};
