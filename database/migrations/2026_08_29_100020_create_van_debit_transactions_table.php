<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_debit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_id')->constrained()->restrictOnDelete();
            // Null for a manual adjustment; set when this row came from finalizing a settlement.
            $table->foreignId('van_settlement_id')->nullable()->constrained()->nullOnDelete();

            // Signed: positive increases what the van owes, negative decreases it.
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);

            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['van_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_debit_transactions');
    }
};
