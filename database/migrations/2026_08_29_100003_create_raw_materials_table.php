<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();

            // How staff physically count it: Packet, Drum, Sack, ...
            $table->string('unit_label')->default('Packet');
            // How much one unit holds, expressed in base_unit. 1 packet = 50 kg.
            $table->decimal('unit_size', 12, 3)->default(1);
            $table->string('base_unit', 10)->default('kg');

            // Balances are kept in units (packets/drums), the way the warehouse counts.
            $table->decimal('opening_stock', 14, 3)->default(0);
            $table->decimal('current_stock', 14, 3)->default(0);
            $table->decimal('reorder_level', 14, 3)->nullable();

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_materials');
    }
};
