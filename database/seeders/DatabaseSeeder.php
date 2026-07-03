<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Main test user (you log in as this one)
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'balance' => 500.00,
        ]);

        // A few other sellers whose products the test user can buy
        $sellers = User::factory(3)->create(['balance' => 100.00]);

        $this->call([
            CategorySeeder::class,
            TagSeeder::class,
        ]);

        // Products by other sellers (test user can buy these)
        $categoryIds = Category::pluck('id');
        $otherProducts = collect();
        foreach ($sellers as $seller) {
            $products = Product::factory(5)->create([
                'user_id' => $seller->id,
                'category_id' => $categoryIds->random(),
            ]);
            $otherProducts = $otherProducts->merge($products);
        }

        // Products listed by test user himself (his own shop)
        Product::factory(4)->create([
            'user_id' => $testUser->id,
            'category_id' => $categoryIds->random(),
        ]);

        // Test user buys some products from other sellers
        $service = new PurchaseService;
        $toBuy = $otherProducts->shuffle()->take(6);

        foreach ($toBuy as $product) {
            try {
                $service->purchase($testUser, $product);
            } catch (\Throwable) {
                // skip if insufficient balance
            }
        }

        // Admin user
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'balance' => 0,
        ]);
    }
}
