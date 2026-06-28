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

new #[Layout('layouts.app'), Title('Product')] class extends Component
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

                <p class="mb-2 text-sm text-zinc-500">
                    by <a href="{{ route('users.show', $this->product->user->username) }}" wire:navigate class="text-zinc-300 transition-colors hover:text-white">{{ $this->product->user->name }}</a>
                </p>

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
                            class="w-full rounded-xl bg-white py-3 text-sm font-semibold text-zinc-900 transition-colors hover:bg-zinc-100"
                        >
                            {{ $this->product->price > 0 ? 'Buy now' : 'Get for free' }}
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

                    <div class="mt-6 border-t border-zinc-800 pt-4 text-xs text-zinc-600">
                        <p>Digital download • Instant access</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-page-background>
