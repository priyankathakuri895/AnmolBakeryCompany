<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->decimal('current_debit_balance', 12, 2)->default(0)->after('registration_number');
        });
    }

    public function down(): void
    {
        Schema::table('vans', function (Blueprint $table) {
            $table->dropColumn('current_debit_balance');
        });
    }
};
