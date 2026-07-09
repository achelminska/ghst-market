<?php

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Computed]
    public function stats(): array
    {
        $user = Auth::user();
        $purchasedIds = $user->purchases()->pluck('product_id');

        return [
            'library'        => $user->purchases()->count(),
            'spent'          => $user->purchases()->sum('price_paid'),
            'listed'         => Product::where('user_id', $user->id)->where('is_active', true)->count(),
            'earned'         => \App\Models\Purchase::whereHas('product', fn ($q) => $q->where('user_id', $user->id))->sum('price_paid'),
        ];
    }

    #[Computed]
    public function recentPurchases()
    {
        return Auth::user()
            ->purchases()
            ->with('product.category')
            ->latest('purchased_at')
            ->limit(4)
            ->get();
    }

    #[Computed]
    public function myProducts()
    {
        return Product::where('user_id', Auth::id())
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();
    }

    #[Computed]
    public function discoverProducts()
    {
        $purchasedIds = Auth::user()->purchases()->pluck('product_id');

        return Product::where('is_active', true)
            ->whereNotIn('id', $purchasedIds)
            ->where('user_id', '!=', Auth::id())
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();
    }
};
?>

<div>
    <flux:main>
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Welcome back, {{ Auth::user()->name }}</h1>
            <p class="mt-1 text-sm text-zinc-500">Here's what's happening with your account.</p>
        </div>

        {{-- Stats --}}
        <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-zinc-500">Library</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $this->stats['library'] }}</p>
                <p class="mt-1 text-xs text-zinc-600">items purchased</p>
            </div>
            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-zinc-500">Spent</p>
                <p class="mt-2 text-3xl font-bold text-white">${{ number_format($this->stats['spent'], 2) }}</p>
                <p class="mt-1 text-xs text-zinc-600">total purchases</p>
            </div>
            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-zinc-500">Listed</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $this->stats['listed'] }}</p>
                <p class="mt-1 text-xs text-zinc-600">active products</p>
            </div>
            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-zinc-500">Earned</p>
                <p class="mt-2 text-3xl font-bold text-white">${{ number_format($this->stats['earned'], 2) }}</p>
                <p class="mt-1 text-xs text-zinc-600">from your products</p>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Recent purchases --}}
            <div class="lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Recent purchases</h2>
                    <a href="{{ route('my-purchases') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-white">View all →</a>
                </div>

                @if ($this->recentPurchases->isEmpty())
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900 px-6 py-12 text-center">
                        <p class="text-sm text-zinc-500">No purchases yet.</p>
                        <a href="{{ route('products.index') }}" wire:navigate class="mt-3 inline-block text-sm text-white underline">Browse products</a>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($this->recentPurchases as $purchase)
                            <div wire:key="{{ $purchase->id }}" class="flex items-center gap-4 rounded-xl border border-zinc-800 bg-zinc-900 p-3">
                                <div class="h-12 w-16 shrink-0 overflow-hidden rounded-lg bg-zinc-800">
                                    @if ($purchase->product->thumbnail)
                                        <img src="{{ $purchase->product->thumbnail }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-200">{{ $purchase->product->title }}</p>
                                    <p class="text-xs text-zinc-600">{{ $purchase->purchased_at->format('d M Y') }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold text-white">
                                        {{ $purchase->price_paid > 0 ? '$'.number_format($purchase->price_paid, 2) : 'Free' }}
                                    </p>
                                    <a
                                        href="{{ route('purchases.download', $purchase) }}"
                                        class="mt-0.5 inline-flex items-center gap-1 text-xs text-zinc-500 transition-colors hover:text-white"
                                    >
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Discover --}}
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Discover</h2>
                    <a href="{{ route('products.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-white">Browse →</a>
                </div>

                <div class="space-y-2">
                    @forelse ($this->discoverProducts as $product)
                        <a wire:key="{{ $product->id }}" href="{{ route('products.show', $product->slug) }}" wire:navigate class="flex items-center gap-3 rounded-xl border border-zinc-800 bg-zinc-900 p-3 transition hover:border-zinc-700">
                            <div class="h-10 w-14 shrink-0 overflow-hidden rounded-md bg-zinc-800">
                                @if ($product->thumbnail)
                                    <img src="{{ $product->thumbnail }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium text-zinc-200">{{ $product->title }}</p>
                                @if ($product->category)
                                    <p class="text-xs text-zinc-600">{{ $product->category->name }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-white">
                                {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                            </span>
                        </a>
                    @empty
                        <p class="text-sm text-zinc-600">Nothing new to discover.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- My products --}}
        @if ($this->myProducts->isNotEmpty())
            <div class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">My products</h2>
                    <a href="{{ route('my-products.index') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-white">Manage →</a>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($this->myProducts as $product)
                        <a wire:key="{{ $product->id }}" href="{{ route('products.show', $product->slug) }}" wire:navigate class="group block rounded-xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700">
                            <div class="aspect-[4/3] w-full overflow-hidden rounded-t-xl bg-zinc-800">
                                @if ($product->thumbnail)
                                    <img src="{{ $product->thumbnail }}" alt="" class="h-full w-full object-cover transition group-hover:scale-105">
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="truncate text-xs font-medium text-zinc-300">{{ $product->title }}</p>
                                <p class="text-xs font-semibold text-white">
                                    {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </flux:main>
</div>
