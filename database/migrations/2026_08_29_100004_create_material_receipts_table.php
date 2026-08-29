<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();

            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('driver_name')->nullable();

            $table->date('received_date');
            $table->string('bill_number')->nullable();
            $table->date('bill_date')->nullable();

            // The physical bill has been checked and filed.
            $table->boolean('bill_stacked')->default(false);
            $table->timestamp('bill_stacked_at')->nullable();

            // A follow-up delivery points back at the receipt it settles.
            $table->string('receipt_type', 20)->default('initial');
            $table->foreignId('parent_receipt_id')->nullable()
                ->constrained('material_receipts')->nullOnDelete();

            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['supplier_id', 'received_date']);
            $table->index('status');
            $table->index('bill_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_receipts');
    }
};
