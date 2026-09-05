<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date');
            // Null for a general business expense not tied to a specific van.
            $table->foreignId('van_id')->nullable()->constrained()->nullOnDelete();
            // Set when recorded from within a settlement; null for a standalone entry.
            $table->foreignId('van_settlement_id')->nullable()->constrained()->nullOnDelete();

            $table->string('category', 30);
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['expense_date', 'category']);
            $table->index('van_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
