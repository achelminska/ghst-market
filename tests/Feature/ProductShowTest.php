<?php

use App\Models\Product;

test('products show page displays product', function () {
    $product = Product::factory()->create();
    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee($product->title);
});

test('inactive product gives 404', function () {
    $product = Product::factory()->inactive()->create();
    $this->get(route('products.show', $product->slug))
        ->assertNotFound();
});
