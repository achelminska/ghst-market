<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'users'    => User::count(),
            'products' => Product::count(),
            'purchases'=> Purchase::count(),
            'revenue'  => Purchase::sum('price_paid'),
        ];
    }

    #[Computed]
    public function recentUsers()
    {
        return User::latest()->limit(5)->get();
    }

    #[Computed]
    public function recentProducts()
    {
        return Product::with('user', 'category')->latest()->limit(5)->get();
    }

    #[Computed]
    public function topProducts()
    {
        return Product::withCount('purchases')
            ->orderByDesc('purchases_count')
            ->limit(5)
            ->get();
    }
};
?>

<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-white">Dashboard</h1>
        <p class="text-sm text-zinc-500">Overview of {{ config('app.name') }}</p>
    </div>

    {{-- Stats --}}
    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total users',    'value' => $this->stats['users'],     'suffix' => ''],
            ['label' => 'Total products', 'value' => $this->stats['products'],  'suffix' => ''],
            ['label' => 'Purchases',      'value' => $this->stats['purchases'], 'suffix' => ''],
            ['label' => 'Revenue',        'value' => '$'.number_format($this->stats['revenue'], 2), 'suffix' => ''],
        ] as $stat)
            <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-zinc-500">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Recent users --}}
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white">Recent users</h2>
                <a href="{{ route('admin.users') }}" class="text-xs text-zinc-500 hover:text-white">View all →</a>
            </div>
            <div class="overflow-hidden rounded-xl border border-zinc-800">
                @foreach ($this->recentUsers as $user)
                    <div wire:key="{{ $user->id }}" class="flex items-center gap-3 border-b border-zinc-800 px-4 py-3 last:border-0">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-700 text-xs font-medium text-white">
                            {{ $user->initials() }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm text-zinc-300">{{ $user->name }}</p>
                            <p class="truncate text-xs text-zinc-600">{{ $user->email }}</p>
                        </div>
                        @if ($user->is_admin)
                            <span class="ml-auto shrink-0 rounded bg-zinc-700 px-1.5 py-0.5 text-xs text-zinc-400">admin</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent products --}}
        <div>
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-white">Recent products</h2>
                <a href="{{ route('admin.products') }}" class="text-xs text-zinc-500 hover:text-white">View all →</a>
            </div>
            <div class="overflow-hidden rounded-xl border border-zinc-800">
                @foreach ($this->recentProducts as $product)
                    <div wire:key="{{ $product->id }}" class="flex items-center gap-3 border-b border-zinc-800 px-4 py-3 last:border-0">
                        <div class="h-8 w-11 shrink-0 overflow-hidden rounded bg-zinc-800">
                            @if ($product->thumbnail)
                                <img src="{{ $product->thumbnail }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-zinc-300">{{ $product->title }}</p>
                            <p class="text-xs text-zinc-600">{{ $product->user->name }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-semibold text-white">
                            {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top selling --}}
        <div>
            <h2 class="mb-3 text-sm font-semibold text-white">Top selling</h2>
            <div class="overflow-hidden rounded-xl border border-zinc-800">
                @foreach ($this->topProducts as $i => $product)
                    <div wire:key="{{ $product->id }}" class="flex items-center gap-3 border-b border-zinc-800 px-4 py-3 last:border-0">
                        <span class="w-4 shrink-0 text-xs text-zinc-600">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-zinc-300">{{ $product->title }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-zinc-500">{{ $product->purchases_count }} sales</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
