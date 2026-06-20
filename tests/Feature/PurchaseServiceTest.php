<?php

use App\Exceptions\AlreadyPurchasedException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Product;
use App\Models\User;
use App\Services\PurchaseService;

test('PurchaseService can purchase a product', function () {
    $seller = User::factory()->create(['balance' => 0]);
    $user = User::factory()->create(['balance' => 100]);
    $product = Product::factory()->create(['price' => 20, 'user_id' => $seller->id]);

    (new PurchaseService)->purchase($user, $product);

    $this->assertDatabaseHas('purchases', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
    expect($user->fresh()->balance)->toEqual('80.00');
    expect($seller->fresh()->balance)->toEqual('20.00');
});

test('User cannot purchase the same product twice', function () {
    $user = User::factory()->create(['balance' => 100]);
    $product = Product::factory()->create(['price' => 20]);
    (new PurchaseService)->purchase($user, $product);
    expect(fn () => (new PurchaseService)->purchase($user, $product))
        ->toThrow(AlreadyPurchasedException::class);
});

test('User cannot purchase a product with insufficient balance', function () {
    $user = User::factory()->create(['balance' => 10]);
    $product = Product::factory()->create(['price' => 20]);
    expect(fn () => (new PurchaseService)->purchase($user, $product))
        ->toThrow(InsufficientBalanceException::class);
});
