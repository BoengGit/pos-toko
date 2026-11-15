<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Sembako', 'Minuman', 'Makanan Ringan', 'Kebutuhan Rumah Tangga', 'Alat Tulis'];
        foreach ($categories as $name) {
            Category::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}
