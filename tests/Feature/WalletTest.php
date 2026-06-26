<?php

use App\Models\Product;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Wallet page shows purchase and sale transactions', function () {
    $seller = User::factory()->create(['balance' => 100]);
    $buyer = User::factory()->create(['balance' => 100]);
    $product = Product::factory()->create(['user_id' => $seller->id, 'price' => 20, 'title' => 'Cool Asset']);

    (new PurchaseService)->purchase($buyer, $product);

    $this->actingAs($seller)
        ->get(route('wallet'))
        ->assertOk()
        ->assertSee('Sale: Cool Asset');

    $this->actingAs($buyer)
        ->get(route('wallet'))
        ->assertOk()
        ->assertSee('Purchased: Cool Asset');
});
