<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = ['free', 'premium', 'minimal', 'dark', 'light', 'retro', 'modern', 'hand-drawn', 'vector'];

        foreach ($tags as $name) {
            Tag::factory()->create(['name' => $name]);
        }
    }
}
