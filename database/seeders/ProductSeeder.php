<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Starter catalog of finished goods. Placeholder items/prices — swap in the
     * real product list once the owner provides it.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'White Bread', 'unit_label' => 'Piece', 'price' => 60],
            ['name' => 'Brown Bread', 'unit_label' => 'Piece', 'price' => 70],
            ['name' => 'Bun', 'unit_label' => 'Piece', 'price' => 15],
            ['name' => 'Cream Roll', 'unit_label' => 'Piece', 'price' => 25],
            ['name' => 'Rusk', 'unit_label' => 'Packet', 'price' => 90],
            ['name' => 'Cake (Small)', 'unit_label' => 'Piece', 'price' => 150],
            ['name' => 'Doughnut', 'unit_label' => 'Piece', 'price' => 30],
            ['name' => 'Khari Biscuit', 'unit_label' => 'Packet', 'price' => 80],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product + ['is_active' => true],
            );
        }
    }
}
