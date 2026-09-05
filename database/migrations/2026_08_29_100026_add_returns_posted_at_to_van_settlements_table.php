<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('van_settlements', function (Blueprint $table) {
            // When returns were counted and locked in — before payment is recorded.
            $table->timestamp('returns_posted_at')->nullable()->after('previous_debit_balance');
        });
    }

    public function down(): void
    {
        Schema::table('van_settlements', function (Blueprint $table) {
            $table->dropColumn('returns_posted_at');
        });
    }
};
