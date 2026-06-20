<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guest is redirected to admin login when visiting admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));
});

test('regular user is redirected to admin login when visiting admin dashboard', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard');
});

test('admin can see users list', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSee('Jane Doe');
});

test('admin can see products list', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::factory()->create(['title' => 'My Test Asset']);

    $this->actingAs($admin)
        ->get(route('admin.products'))
        ->assertOk()
        ->assertSee('My Test Asset');
});

test('admin can access categories page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.categories'))
        ->assertOk()
        ->assertSee('Categories');
});

test('admin login page is accessible', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertSee('Admin panel');
});

test('admin can toggle product active status', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test('pages::admin.products')
        ->call('toggleActive', $product->id);

    expect($product->fresh()->is_active)->toBeFalse();
});
