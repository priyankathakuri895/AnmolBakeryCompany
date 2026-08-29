<?php

namespace Database\Seeders;

use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class RawMaterialSeeder extends Seeder
{
    /**
     * The materials the business currently buys, with their standard packaging.
     */
    public function run(): void
    {
        $materials = [
            ['name' => 'Flour',      'unit_label' => 'Packet', 'unit_size' => 50,  'base_unit' => 'kg'],
            ['name' => 'Sugar',      'unit_label' => 'Packet', 'unit_size' => 50,  'base_unit' => 'kg'],
            ['name' => 'Yeast',      'unit_label' => 'Packet', 'unit_size' => 500, 'base_unit' => 'g'],
            ['name' => 'Calcium',    'unit_label' => 'Packet', 'unit_size' => 1,   'base_unit' => 'kg'],
            ['name' => 'Butter',     'unit_label' => 'Packet', 'unit_size' => 500, 'base_unit' => 'g'],
            ['name' => 'Oil',        'unit_label' => 'Drum',   'unit_size' => 20,  'base_unit' => 'l'],
            ['name' => 'Puff Ghee',  'unit_label' => 'Packet', 'unit_size' => 25,  'base_unit' => 'kg'],
            ['name' => 'Cream Ghee', 'unit_label' => 'Packet', 'unit_size' => 25,  'base_unit' => 'kg'],
        ];

        foreach ($materials as $material) {
            RawMaterial::updateOrCreate(
                ['name' => $material['name']],
                $material + ['is_active' => true],
            );
        }
    }
}
