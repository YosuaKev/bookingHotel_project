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
        Schema::table('rooms', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('rooms', 'capacity')) {
                $table->integer('capacity')->default(2)->after('room_type');
            }
            if (!Schema::hasColumn('rooms', 'air_conditioning')) {
                $table->boolean('air_conditioning')->default(true)->after('wifi');
            }
            if (!Schema::hasColumn('rooms', 'tv')) {
                $table->boolean('tv')->default(true)->after('air_conditioning');
            }
            if (!Schema::hasColumn('rooms', 'bathroom_type')) {
                $table->string('bathroom_type')->default('private')->after('tv');
            }
            if (!Schema::hasColumn('rooms', 'amenities')) {
                $table->json('amenities')->nullable()->after('bathroom_type');
            }
            if (!Schema::hasColumn('rooms', 'status')) {
                $table->string('status')->default('available')->after('amenities');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'capacity')) {
                $table->dropColumn('capacity');
            }
            if (Schema::hasColumn('rooms', 'air_conditioning')) {
                $table->dropColumn('air_conditioning');
            }
            if (Schema::hasColumn('rooms', 'tv')) {
                $table->dropColumn('tv');
            }
            if (Schema::hasColumn('rooms', 'bathroom_type')) {
                $table->dropColumn('bathroom_type');
            }
            if (Schema::hasColumn('rooms', 'amenities')) {
                $table->dropColumn('amenities');
            }
            if (Schema::hasColumn('rooms', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
