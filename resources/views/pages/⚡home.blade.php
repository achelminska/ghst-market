<?php

use App\Models\Product;
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
};
?>

<div>
    {{-- Hero --}}
    <section class="mx-auto max-w-7xl px-6 pb-4 pt-0 text-center sm:py-12">
        {{-- Mobile: pixel art logo --}}
        <img src="{{ asset('images/ghst-market-logo-pixel.png') }}" alt="GHST Market" class="mx-auto mb-4 w-80 mix-blend-screen sm:hidden">
        {{-- Desktop: original logo --}}
        <img src="{{ asset('images/ghst-market-logo.png') }}" alt="GHST Market" class="mx-auto mb-4 hidden h-64 w-auto mix-blend-screen sm:block lg:h-80">

        <p class="mx-auto mt-6 max-w-lg text-lg text-zinc-500">
            Browse and download fonts, templates, illustrations, music and indie game files — or sell your own work.
        </p>

        <div class="mt-10 flex items-center justify-center gap-4">
            <a
                href="{{ route('products.index') }}"
                wire:navigate
                class="rounded-xl bg-white px-6 py-3 text-sm font-semibold text-zinc-900 shadow transition-colors hover:bg-zinc-100"
            >
                Browse products
            </a>

            @guest
                <a
                    href="{{ route('register') }}"
                    wire:navigate
                    class="rounded-xl border border-zinc-700 px-6 py-3 text-sm font-semibold text-zinc-300 transition-colors hover:border-zinc-500 hover:text-white"
                >
                    Start selling
                </a>
            @endguest
        </div>
    </section>

    {{-- Products grid --}}
    @if ($this->featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-6 pb-24">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-base font-semibold text-white">Latest products</h2>
                <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-zinc-500 transition-colors hover:text-white">
                    View all →
                </a>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($this->featuredProducts as $product)
                    <a
                        href="{{ route('products.show', $product->slug) }}"
                        wire:navigate
                        class="group block rounded-2xl border border-zinc-800 bg-zinc-900 p-4 transition hover:border-zinc-700 hover:bg-zinc-800/60"
                    >
                        <div class="mb-4 flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-xl bg-zinc-800">
                            @if ($product->thumbnail)
                                <img src="{{ $product->thumbnail }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                            @else
                                <svg class="size-10 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 16.5V19a.75.75 0 00.75.75h16.5A.75.75 0 0021 19v-2.5M3 16.5V7.5A.75.75 0 013.75 6.75h16.5A.75.75 0 0121 7.5v9" />
                                </svg>
                            @endif
                        </div>

                        <p class="truncate text-sm font-medium text-zinc-200 transition-colors group-hover:text-white">
                            {{ $product->title }}
                        </p>

                        <div class="mt-1 flex items-center justify-between">
                            <span class="text-sm font-semibold text-white">
                                {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                            </span>

                            @if ($product->category)
                                <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-500">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
