<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_receipts', function (Blueprint $table) {
            $table->string('bill_image_path')->nullable()->after('bill_stacked_at');
        });
    }

    public function down(): void
    {
        Schema::table('material_receipts', function (Blueprint $table) {
            $table->dropColumn('bill_image_path');
        });
    }
};
