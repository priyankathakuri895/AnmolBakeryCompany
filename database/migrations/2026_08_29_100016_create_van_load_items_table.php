<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_load_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_load_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->decimal('quantity', 14, 3);
            // Snapshot of the product's selling price at load time.
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->unique(['van_load_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_load_items');
    }
};
