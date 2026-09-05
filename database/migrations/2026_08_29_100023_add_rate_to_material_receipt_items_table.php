<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_receipt_items', function (Blueprint $table) {
            // Price per counting unit (packet/drum/...), as billed. Amount = bill_qty * rate.
            $table->decimal('rate', 12, 2)->default(0)->after('bill_qty');
        });
    }

    public function down(): void
    {
        Schema::table('material_receipt_items', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
    }
};
