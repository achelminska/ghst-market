<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seller profile page is publicly accessible', function () {
    $seller = User::factory()->create(['name' => 'Jane Seller']);

    $this->get(route('users.show', $seller->username))
        ->assertOk()
        ->assertSee('Jane Seller')
        ->assertSee('@'.$seller->username);
});

test('seller profile shows only active products', function () {
    $seller = User::factory()->create();
    Product::factory()->create(['user_id' => $seller->id, 'title' => 'Active Product', 'is_active' => true]);
    Product::factory()->create(['user_id' => $seller->id, 'title' => 'Hidden Product', 'is_active' => false]);

    $this->get(route('users.show', $seller->username))
        ->assertOk()
        ->assertSee('Active Product')
        ->assertDontSee('Hidden Product');
});

test('seller profile returns 404 for unknown username', function () {
    $this->get(route('users.show', 'nobody-here'))
        ->assertNotFound();
});

test('username is auto-generated from name on user creation', function () {
    $user = User::factory()->create(['name' => 'Alice Bob']);

    expect($user->username)->toBe('alice-bob');
});
