<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->string('type', 20);
            // Signed: positive adds to stock, negative removes.
            $table->decimal('quantity', 14, 3);
            $table->decimal('balance_after', 14, 3);

            // Whatever caused this movement: a van load, a return, a stock check, ...
            $table->nullableMorphs('reference');

            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'transaction_date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_transactions');
    }
};
