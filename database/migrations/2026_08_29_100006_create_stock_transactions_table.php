<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')->constrained()->restrictOnDelete();

            $table->string('type', 20);
            // Signed: positive adds to stock, negative removes. In counting units.
            $table->decimal('quantity', 14, 3);
            $table->decimal('balance_after', 14, 3);

            $table->decimal('unit_size', 12, 3)->default(1);
            $table->decimal('base_quantity', 16, 3)->default(0);
            $table->string('base_unit', 10)->nullable();

            // Whatever caused this movement: a receipt item, a stock check item, ...
            $table->nullableMorphs('reference');

            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['raw_material_id', 'transaction_date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
