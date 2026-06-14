<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Fonts', 'Icons', 'UI Kits', 'Templates', 'Illustrations', 'Mockups'];

        foreach ($categories as $name) {
            Category::factory()->create(['name' => $name]);
        }
    }
}
