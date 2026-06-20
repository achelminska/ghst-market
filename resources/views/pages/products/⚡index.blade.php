<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $price = '';

    #[Url]
    public string $sort = 'newest';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedPrice(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search, fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($this->search).'%']))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->price === 'free', fn ($q) => $q->where('price', 0))
            ->when($this->price === 'paid', fn ($q) => $q->where('price', '>', 0))
            ->with(['category'])
            ->when($this->sort === 'newest', fn ($q) => $q->latest())
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->paginate(24);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }
};
?>

<div>
    <flux:main class="!p-0">
        <div class="flex min-h-screen">
            {{-- Filter sidebar --}}
            <aside class="hidden w-52 shrink-0 border-e border-zinc-800 px-4 py-6 lg:block xl:w-60">
                <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-zinc-500">Filter results</p>

                {{-- Search --}}
                <div class="mb-6">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search..."
                        size="sm"
                        icon="magnifying-glass"
                        clearable
                    />
                </div>

                {{-- Price --}}
                <div class="mb-6">
                    <p class="mb-2 text-xs font-medium text-zinc-400">Price</p>
                    <ul class="space-y-1">
                        <li>
                            <button wire:click="$set('price', '')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $price === '' ? 'text-white' : 'text-zinc-400' }}">
                                All
                            </button>
                        </li>
                        <li>
                            <button wire:click="$set('price', 'free')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $price === 'free' ? 'text-white' : 'text-zinc-400' }}">
                                Free
                            </button>
                        </li>
                        <li>
                            <button wire:click="$set('price', 'paid')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $price === 'paid' ? 'text-white' : 'text-zinc-400' }}">
                                Paid
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Categories --}}
                <div class="mb-6">
                    <p class="mb-2 text-xs font-medium text-zinc-400">Category</p>
                    <ul class="space-y-1">
                        <li>
                            <button wire:click="$set('category', '')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $category === '' ? 'text-white' : 'text-zinc-400' }}">
                                All categories
                            </button>
                        </li>
                        @foreach ($this->categories as $cat)
                            <li>
                                <button wire:key="{{ $cat->id }}" wire:click="$set('category', '{{ $cat->id }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $category === $cat->id ? 'text-white' : 'text-zinc-400' }}">
                                    {{ $cat->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Sort --}}
                <div>
                    <p class="mb-2 text-xs font-medium text-zinc-400">Sort by</p>
                    <ul class="space-y-1">
                        @foreach (['newest' => 'Most recent', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low'] as $value => $label)
                            <li>
                                <button wire:click="$set('sort', '{{ $value }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $sort === $value ? 'text-white' : 'text-zinc-400' }}">
                                    {{ $label }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            {{-- Product grid --}}
            <div class="flex-1 px-6 py-6">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-zinc-500">{{ $this->products->total() }} results</p>

                    {{-- Mobile filters --}}
                    <div class="flex gap-2 lg:hidden">
                        <flux:select wire:model.live="category" size="sm" placeholder="Category">
                            <flux:select.option value="">All categories</flux:select.option>
                            @foreach ($this->categories as $cat)
                                <flux:select.option wire:key="{{ $cat->id }}" value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                    @forelse ($this->products as $product)
                        <a
                            wire:key="{{ $product->id }}"
                            href="{{ route('products.show', $product->slug) }}"
                            wire:navigate
                            class="group block rounded-lg border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700 hover:bg-zinc-800/60"
                        >
                            <div class="aspect-square w-full overflow-hidden rounded-t-lg bg-zinc-800">
                                @if ($product->thumbnail)
                                    <img src="{{ $product->thumbnail }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-center justify-center">
                                        <svg class="size-6 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 16.5V19a.75.75 0 00.75.75h16.5A.75.75 0 0021 19v-2.5M3 16.5V7.5A.75.75 0 013.75 6.75h16.5A.75.75 0 0121 7.5v9" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-2">
                                <p class="truncate text-xs font-medium text-zinc-300 group-hover:text-white">{{ $product->title }}</p>
                                <div class="mt-0.5 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-white">
                                        {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                                    </span>
                                    @if ($product->category)
                                        <span class="truncate text-xs text-zinc-600">{{ $product->category->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-20 text-center">
                            <p class="text-zinc-500">No products found.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $this->products->links() }}
                </div>
            </div>
        </div>
    </flux:main>
</div>
