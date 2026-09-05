<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_load_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('draft');

            $table->decimal('cash_collected', 12, 2)->default(0);
            $table->decimal('online_collected', 12, 2)->default(0);
            $table->decimal('debit_collected', 12, 2)->default(0); // paid off old debit
            $table->decimal('new_debit_given', 12, 2)->default(0); // new credit sales today
            // Snapshot of the van's debit balance when this settlement was started.
            $table->decimal('previous_debit_balance', 12, 2)->default(0);

            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_settlements');
    }
};
