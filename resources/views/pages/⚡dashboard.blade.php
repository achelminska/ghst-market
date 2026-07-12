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
            @foreach ([
                ['label' => 'Library', 'value' => $this->stats['library'], 'caption' => 'items purchased', 'icon' => 'M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776'],
                ['label' => 'Spent', 'value' => '$'.number_format($this->stats['spent'], 2), 'caption' => 'total purchases', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                ['label' => 'Listed', 'value' => $this->stats['listed'], 'caption' => 'active products', 'icon' => 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
                ['label' => 'Earned', 'value' => '$'.number_format($this->stats['earned'], 2), 'caption' => 'from your products', 'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ] as $stat)
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5 transition duration-200 hover:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium uppercase tracking-widest text-zinc-500">{{ $stat['label'] }}</p>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-accent/10">
                            <svg class="size-4 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-2 text-3xl font-bold text-white">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-xs text-zinc-600">{{ $stat['caption'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Recent purchases --}}
            <div class="lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white">Recent purchases</h2>
                    <a href="{{ route('my-purchases') }}" wire:navigate class="text-xs text-zinc-500 transition-colors hover:text-white">View all →</a>
                </div>

                @if ($this->recentPurchases->isEmpty())
                    <div class="flex flex-col items-center rounded-xl border border-dashed border-zinc-800 bg-zinc-900/50 px-6 py-12 text-center">
                        <img src="{{ asset('images/ghst-icon.png') }}" alt="" class="mb-3 h-12 w-auto opacity-30 mix-blend-screen">
                        <p class="text-sm text-zinc-500">Your library is empty.</p>
                        <a href="{{ route('products.index') }}" wire:navigate class="mt-4 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground transition-colors hover:bg-green-300">Browse products</a>
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
