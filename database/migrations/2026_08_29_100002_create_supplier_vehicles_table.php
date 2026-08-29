<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_number');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['supplier_id', 'vehicle_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_vehicles');
    }
};
