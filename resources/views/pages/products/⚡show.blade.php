<?php

use App\Exceptions\AlreadyPurchasedException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OwnProductPurchaseException;
use App\Models\Product;
use App\Services\PurchaseService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.guest'), Title('Product')] class extends Component
{
    public Product $product;
    public string $errorMessage = '';

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'tags', 'user'])
            ->firstOrFail();
    }

    #[Computed]
    public function alreadyPurchased(): bool
    {
        return Auth::check() && Auth::user()->purchases()
            ->where('product_id', $this->product->id)
            ->exists();
    }

    #[Computed]
    public function relatedProducts()
    {
        return Product::query()
            ->where('is_active', true)
            ->where('category_id', $this->product->category_id)
            ->whereKeyNot($this->product->id)
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();
    }

    #[Computed]
    public function sellerProductsCount(): int
    {
        return $this->product->user->products()->where('is_active', true)->count();
    }

    public function purchase(PurchaseService $service): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        try {
            $service->purchase(Auth::user(), $this->product);
            unset($this->alreadyPurchased);
        } catch (OwnProductPurchaseException) {
            $this->errorMessage = 'You cannot purchase your own product.';
        } catch (AlreadyPurchasedException) {
            $this->errorMessage = 'You already own this product.';
        } catch (InsufficientBalanceException) {
            $this->errorMessage = 'Insufficient balance.';
        }
    }
};
?>

<x-page-background :image-url="$this->product->cover_image">
    <div class="px-6 py-8">
        <a
            href="{{ route('products.index') }}"
            wire:navigate
            class="mb-6 inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-white"
        >
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Back to products
        </a>

        <div class="grid gap-10 lg:grid-cols-3">
            {{-- Left: content --}}
            <div class="lg:col-span-2">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-400">
                        {{ $this->product->category->name }}
                    </span>
                    @foreach ($this->product->tags as $tag)
                        <a
                            wire:key="{{ $tag->id }}"
                            href="{{ route('products.index', ['tag' => $tag->slug]) }}"
                            wire:navigate
                            class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-500 transition hover:border-zinc-500 hover:text-zinc-300"
                        >
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>

                <h1 class="mb-3 text-3xl font-bold tracking-tight text-white lg:text-4xl">
                    {{ $this->product->title }}
                </h1>

                {{-- Seller card --}}
                <a
                    href="{{ route('users.show', $this->product->user->username) }}"
                    wire:navigate
                    class="group mt-4 inline-flex items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-900/60 px-4 py-3 transition duration-200 hover:border-accent/40"
                >
                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-zinc-700">
                        @if ($this->product->user->avatarUrl())
                            <img src="{{ $this->product->user->avatarUrl() }}" alt="{{ $this->product->user->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-sm font-bold text-white">
                                {{ $this->product->user->initials() }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-200 transition-colors group-hover:text-white">{{ $this->product->user->name }}</p>
                        <p class="text-xs text-zinc-500">{{ '@'.$this->product->user->username }} &middot; {{ $this->sellerProductsCount }} {{ $this->sellerProductsCount === 1 ? 'product' : 'products' }}</p>
                    </div>
                </a>

                <div class="mt-8 border-t border-zinc-800 pt-8">
                    <p class="whitespace-pre-line leading-relaxed text-zinc-400">{{ $this->product->description }}</p>
                </div>
            </div>

            {{-- Right: purchase card --}}
            <div class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                    @if ($this->product->thumbnail)
                        <div class="mb-4 overflow-hidden rounded-xl border border-zinc-800">
                            <img src="{{ $this->product->thumbnail }}" alt="{{ $this->product->title }}" class="aspect-video w-full object-cover">
                        </div>
                    @endif

                    <div class="mb-6">
                        <p class="text-3xl font-bold text-white">
                            {{ $this->product->price > 0 ? '$'.number_format($this->product->price, 2) : 'Free' }}
                        </p>
                    </div>

                    @if ($this->alreadyPurchased)
                        <div class="mb-3 rounded-xl bg-zinc-800 py-3 text-center text-sm font-medium text-zinc-400">
                            Already in your library
                        </div>
                        <a
                            href="{{ route('dashboard') }}"
                            wire:navigate
                            class="block text-center text-sm text-zinc-500 transition-colors hover:text-white"
                        >
                            Go to dashboard →
                        </a>
                    @else
                        <button
                            wire:click="purchase"
                            wire:loading.attr="disabled"
                            class="w-full rounded-xl bg-accent py-3 text-sm font-semibold text-accent-foreground shadow-lg shadow-accent/20 transition duration-200 hover:bg-green-300 disabled:cursor-wait disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="purchase">{{ $this->product->price > 0 ? 'Buy now' : 'Get for free' }}</span>
                            <span wire:loading wire:target="purchase">Processing...</span>
                        </button>

                        @guest
                            <p class="mt-3 text-center text-xs text-zinc-600">
                                <a href="{{ route('login') }}" wire:navigate class="underline hover:text-zinc-400">Log in</a>
                                to purchase
                            </p>
                        @endguest
                    @endif

                    @if ($errorMessage)
                        <p class="mt-3 text-center text-sm text-red-400">{{ $errorMessage }}</p>
                    @endif

                    <ul class="mt-6 space-y-2 border-t border-zinc-800 pt-4 text-xs text-zinc-500">
                        <li class="flex items-center gap-2">
                            <svg class="size-3.5 shrink-0 text-accent" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" /></svg>
                            Secure digital download
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-3.5 shrink-0 text-accent" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" /></svg>
                            Instant access after purchase
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="size-3.5 shrink-0 text-accent" fill="currentColor" viewBox="0 0 20 20"><path d="M10.75 10.818v2.614A3.13 3.13 0 0 0 11.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 0 0-1.138-.432ZM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 0 0-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.202.592.037.051.08.102.128.152Z" /><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM10.75 5.75a.75.75 0 0 0-1.5 0v.316a3.78 3.78 0 0 0-1.653.713c-.426.33-.744.74-.925 1.2a2.7 2.7 0 0 0-.064 1.798c.111.335.29.652.532.933.181.21.392.406.63.579.437.316.956.545 1.48.677v2.97a2.92 2.92 0 0 1-.937-.256 2.24 2.24 0 0 1-.542-.36.75.75 0 0 0-1.075 1.046c.31.318.68.573 1.088.762.386.178.797.293 1.216.34l.25.03v.312a.75.75 0 0 0 1.5 0v-.317c.607-.087 1.194-.302 1.703-.634.826-.54 1.297-1.35 1.297-2.234 0-.885-.47-1.694-1.297-2.234a4.63 4.63 0 0 0-1.703-.634V7.334c.34.07.663.198.943.383l.026.017a.75.75 0 1 0 .861-1.229 4.42 4.42 0 0 0-1.83-.7V5.75Z" clip-rule="evenodd" /></svg>
                            Pay from your wallet balance
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Related products --}}
        @if ($this->relatedProducts->isNotEmpty())
            <div class="mt-16 border-t border-zinc-800 pt-10">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="font-display text-base text-white">More in {{ $this->product->category->name }}</h2>
                    <a href="{{ route('products.index', ['category' => $this->product->category_id]) }}" wire:navigate class="text-sm text-zinc-500 transition-colors hover:text-accent">
                        View all →
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @foreach ($this->relatedProducts as $related)
                        <x-product-card :product="$related" wire:key="related-{{ $related->id }}" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-page-background>
