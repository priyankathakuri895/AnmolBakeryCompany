<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained()->restrictOnDelete();

            // Set when this line settles an earlier line (short or damaged quantity).
            $table->foreignId('parent_item_id')->nullable()
                ->constrained('material_receipt_items')->nullOnDelete();
            $table->string('link_type', 30)->default('initial');

            // All quantities are in the material's counting unit (packets/drums).
            $table->decimal('bill_qty', 14, 3)->default(0);
            $table->decimal('received_qty', 14, 3)->default(0);
            $table->decimal('damaged_qty', 14, 3)->default(0);
            $table->decimal('accepted_qty', 14, 3)->default(0);   // received - damaged
            $table->decimal('pending_qty', 14, 3)->default(0);    // still owed on this line

            // Unit definition snapshot, so history stays correct if the packaging changes later.
            $table->string('unit_label')->nullable();
            $table->decimal('unit_size', 12, 3)->default(1);
            $table->string('base_unit', 10)->nullable();
            $table->decimal('base_qty', 16, 3)->default(0);       // accepted_qty * unit_size

            $table->string('status', 20)->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['raw_material_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_receipt_items');
    }
};
