<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_id')->constrained()->restrictOnDelete();
            // Who actually drove it that day — can differ from the van's default salesman.
            $table->foreignId('salesman_id')->constrained()->restrictOnDelete();
            $table->date('load_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['van_id', 'load_date']);
            $table->index('load_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_loads');
    }
};
