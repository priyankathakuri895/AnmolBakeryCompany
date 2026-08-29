<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_check_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained()->restrictOnDelete();

            $table->decimal('system_qty', 14, 3)->default(0);
            $table->decimal('physical_qty', 14, 3)->default(0);
            $table->decimal('difference', 14, 3)->default(0); // physical - system

            $table->string('reason', 30)->nullable();
            $table->text('reason_note')->nullable();

            // True once the adjustment transaction has been written.
            $table->boolean('adjusted')->default(false);
            $table->timestamps();

            $table->unique(['stock_check_id', 'raw_material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_check_items');
    }
};
