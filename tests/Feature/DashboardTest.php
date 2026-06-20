<?php

use App\Models\Product;
use App\Models\User;
use App\Services\PurchaseService;

test('Dashboard page displays purchases', function () {
    $user = User::factory()->create(['balance' => 100]);
    $product = Product::factory()->create(['price' => 20]);
    (new PurchaseService)->purchase($user, $product);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($product->title);
});

test('Guest is redirected to login when visiting dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
