<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::factory()->create([
            'name' => 'Demo Seller',
            'email' => 'seller@example.com',
        ]);

        $categories = Category::all();
        $tags = Tag::all();

        Product::factory(12)
            ->recycle($categories)
            ->state(['user_id' => $seller->id])
            ->create()
            ->each(fn (Product $product) => $product->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')
            ));

        Product::factory(3)
            ->free()
            ->recycle($categories)
            ->state(['user_id' => $seller->id])
            ->create()
            ->each(fn (Product $product) => $product->tags()->attach(
                $tags->random(2)->pluck('id')
            ));
    }
}
