<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_receipts', function (Blueprint $table) {
            // bill_image_path already exists (added by an earlier migration on this
            // branch); only the bill-reader columns are new here.
            // What the bill reader returned, stored as-is so a wrong
            // reading can always be compared against what was saved.
            $table->json('bill_extraction')->nullable()->after('bill_image_path');
            $table->timestamp('bill_read_at')->nullable()->after('bill_extraction');
        });
    }

    public function down(): void
    {
        Schema::table('material_receipts', function (Blueprint $table) {
            $table->dropColumn(['bill_extraction', 'bill_read_at']);
        });
    }
};
