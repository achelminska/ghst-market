<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Computed]
    public function featuredProducts()
    {
        return Product::with('category')
            ->where('is_active', true)
            ->latest()
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    /** @return array{products: int, creators: int, free: int} */
    #[Computed]
    public function stats(): array
    {
        return [
            'products' => Product::where('is_active', true)->count(),
            'creators' => User::where('is_admin', false)->whereHas('products', fn ($q) => $q->where('is_active', true))->count(),
            'free' => Product::where('is_active', true)->where('price', 0)->count(),
        ];
    }
};
?>

<div>
    {{-- Hero --}}
    <section class="mx-auto max-w-7xl px-6 pb-4 pt-0 text-center sm:py-12">
        {{-- Mobile: pixel art logo --}}
        <img src="{{ asset('images/ghst-market-logo-pixel.png') }}" alt="GHST Market" class="mx-auto mb-4 w-80 mix-blend-screen sm:hidden">
        {{-- Desktop: original logo --}}
        <img src="{{ asset('images/ghst-market-logo.png') }}" alt="GHST Market" class="mx-auto mb-4 hidden h-64 w-auto mix-blend-screen sm:block lg:h-80">

        <h1 class="mx-auto mt-4 max-w-2xl font-display text-lg text-zinc-200 sm:text-xl">
            Digital assets from indie creators
        </h1>

        <p class="mx-auto mt-4 max-w-lg text-lg text-zinc-500">
            Browse and download fonts, templates, illustrations, music and indie game files — or sell your own work.
        </p>

        <div class="mt-8 flex items-center justify-center gap-4">
            <a
                href="{{ route('products.index') }}"
                wire:navigate
                class="rounded-xl bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-lg shadow-accent/20 transition duration-200 hover:bg-green-300"
            >
                Browse products
            </a>

            @guest
                <a
                    href="{{ route('register') }}"
                    wire:navigate
                    class="rounded-xl border border-zinc-700 px-6 py-3 text-sm font-semibold text-zinc-300 transition duration-200 hover:border-accent/50 hover:text-white"
                >
                    Start selling
                </a>
            @endguest
        </div>

        {{-- Category pills --}}
        @if ($this->categories->isNotEmpty())
            <div class="mt-10 flex flex-wrap items-center justify-center gap-2">
                @foreach ($this->categories as $cat)
                    <a
                        href="{{ route('products.index', ['category' => $cat->id]) }}"
                        wire:navigate
                        class="rounded-full border border-zinc-800 bg-zinc-900 px-4 py-1.5 text-sm text-zinc-400 transition duration-200 hover:border-accent/40 hover:text-accent"
                    >
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Stats --}}
        <div class="mx-auto mt-12 grid max-w-lg grid-cols-3 gap-4 border-t border-zinc-800/60 pt-8">
            <div>
                <p class="font-display text-xl text-accent">{{ $this->stats['products'] }}</p>
                <p class="mt-1 text-xs uppercase tracking-widest text-zinc-500">Products</p>
            </div>
            <div>
                <p class="font-display text-xl text-accent">{{ $this->stats['creators'] }}</p>
                <p class="mt-1 text-xs uppercase tracking-widest text-zinc-500">Creators</p>
            </div>
            <div>
                <p class="font-display text-xl text-accent">{{ $this->stats['free'] }}</p>
                <p class="mt-1 text-xs uppercase tracking-widest text-zinc-500">Free assets</p>
            </div>
        </div>
    </section>

    {{-- Products grid --}}
    @if ($this->featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-6 pb-24 pt-12">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="font-display text-base text-white">Latest products</h2>
                <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-zinc-500 transition-colors hover:text-accent">
                    View all →
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($this->featuredProducts as $product)
                    <x-product-card :product="$product" wire:key="{{ $product->id }}" />
                @endforeach
            </div>
        </section>
    @endif
</div>
