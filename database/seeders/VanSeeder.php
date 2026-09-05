<?php

namespace Database\Seeders;

use App\Models\Salesman;
use App\Models\Van;
use Illuminate\Database\Seeder;

class VanSeeder extends Seeder
{
    /**
     * The 12-van delivery fleet, each defaulted to its matching placeholder salesman.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $salesman = Salesman::where('name', "Salesman {$i}")->first();

            Van::updateOrCreate(
                ['name' => "Van {$i}"],
                [
                    'default_salesman_id' => $salesman?->id,
                    'is_active' => true,
                ],
            );
        }
    }
}
