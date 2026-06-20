<?php

use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');

Route::livewire('/products', 'pages::products.index')->name('products.index');

Route::livewire('/products/{slug}', 'pages::products.show')->name('products.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/my-purchases', 'pages::my-purchases')->name('my-purchases');
    Route::livewire('/my-products', 'pages::my-products.index')->name('my-products.index');
    Route::livewire('/my-products/create', 'pages::my-products.create')->name('my-products.create');
    Route::livewire('/my-products/{slug}/edit', 'pages::my-products.edit')->name('my-products.edit');
    Route::livewire('/wallet', 'pages::wallet')->name('wallet');
    Route::get('/purchases/{purchase}/download', DownloadController::class)->name('purchases.download');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::livewire('/categories', 'pages::admin.categories')->name('admin.categories');
    Route::livewire('/tags', 'pages::admin.tags')->name('admin.tags');
});

// Admin panel (separate login + protected by admin middleware)
Route::prefix('admin')->group(function () {
    Route::livewire('/login', 'pages::admin.login')->name('admin.login');

    Route::middleware(['admin'])->group(function () {
        Route::livewire('/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');
        Route::livewire('/users', 'pages::admin.users')->name('admin.users');
        Route::livewire('/products', 'pages::admin.products')->name('admin.products');
        Route::livewire('/categories', 'pages::admin.categories')->name('admin.categories');
        Route::livewire('/tags', 'pages::admin.tags')->name('admin.tags');
    });
});

require __DIR__.'/settings.php';
