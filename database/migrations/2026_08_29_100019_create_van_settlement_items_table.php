<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('van_load_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->decimal('qty_loaded', 14, 3)->default(0);
            $table->decimal('qty_returned_fresh', 14, 3)->default(0);
            $table->decimal('qty_returned_expired', 14, 3)->default(0);
            $table->decimal('qty_sold', 14, 3)->default(0); // loaded - fresh - expired

            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0); // qty_sold * unit_price
            $table->timestamps();

            $table->unique(['van_settlement_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_settlement_items');
    }
};
