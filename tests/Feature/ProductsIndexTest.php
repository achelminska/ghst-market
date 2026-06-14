<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

test('products page is accessible to guests', function () {
    $this->get('/products')->assertOk();
});

test('active products are visible', function () {
    $product = Product::factory()->create(['title' => 'Cool Font Pack']);

    $this->get('/products')->assertSee('Cool Font Pack');
});

test('inactive products are not visible', function () {
    $product = Product::factory()->inactive()->create(['title' => 'Hidden Product']);

    $this->get('/products')->assertDontSee('Hidden Product');
});

test('search filters products by title', function () {
    Product::factory()->create(['title' => 'Amazing Icon Set']);
    Product::factory()->create(['title' => 'Cool Font Pack']);

    Livewire::test('pages::products.index')
        ->set('search', 'Icon')
        ->assertSee('Amazing Icon Set')
        ->assertDontSee('Cool Font Pack');
});

test('category filter works', function () {
    $icons = Category::factory()->create(['name' => 'Icons']);
    $fonts = Category::factory()->create(['name' => 'Fonts']);

    Product::factory()->create(['title' => 'Icon Set', 'category_id' => $icons->id]);
    Product::factory()->create(['title' => 'Font Pack', 'category_id' => $fonts->id]);

    Livewire::test('pages::products.index')
        ->set('category', $icons->id)
        ->assertSee('Icon Set')
        ->assertDontSee('Font Pack');
});
