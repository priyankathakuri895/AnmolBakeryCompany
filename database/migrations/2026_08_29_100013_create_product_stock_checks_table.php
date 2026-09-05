<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stock_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_no')->unique();
            $table->date('check_date');
            $table->string('status', 20)->default('draft');
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'check_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_checks');
    }
};
