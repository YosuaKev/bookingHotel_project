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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'proof_file')) {
                $table->string('proof_file')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('proof_file');
            }
            if (!Schema::hasColumn('payments', 'verified_comment')) {
                $table->text('verified_comment')->nullable()->after('verified_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'proof_file')) {
                $table->dropColumn('proof_file');
            }
            if (Schema::hasColumn('payments', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
            if (Schema::hasColumn('payments', 'verified_comment')) {
                $table->dropColumn('verified_comment');
            }
        });
    }
};
