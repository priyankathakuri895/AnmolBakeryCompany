<?php

namespace Database\Seeders;

use App\Models\Salesman;
use Illuminate\Database\Seeder;

class SalesmanSeeder extends Seeder
{
    /**
     * One placeholder salesman per van. Swap in the real staff roster later.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Salesman::updateOrCreate(
                ['name' => "Salesman {$i}"],
                ['is_active' => true],
            );
        }
    }
}
