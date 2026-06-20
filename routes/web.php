<?php

use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');

Route::livewire('/products', 'pages::products.index')->name('products.index');

Route::livewire('/products/{slug}', 'pages::products.show')->name('products.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::get('/purchases/{purchase}/download', DownloadController::class)->name('purchases.download');
});

require __DIR__.'/settings.php';
