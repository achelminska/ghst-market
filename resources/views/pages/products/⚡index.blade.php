<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'products';

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $price = '';

    #[Url]
    public string $sort = 'newest';

    #[Url]
    public string $tag = '';

    #[Url]
    public string $creatorCategory = '';

    #[Url]
    public string $creatorSort = 'popular';

    public function updatedTab(): void
    {
        $this->resetPage();
        $this->search = '';
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategory(): void { $this->resetPage(); }
    public function updatedPrice(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }
    public function updatedTag(): void { $this->resetPage(); }
    public function updatedCreatorCategory(): void { $this->resetPage(); }
    public function updatedCreatorSort(): void { $this->resetPage(); }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search, fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($this->search).'%']))
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->price === 'free', fn ($q) => $q->where('price', 0))
            ->when($this->price === 'paid', fn ($q) => $q->where('price', '>', 0))
            ->when($this->tag, fn ($q) => $q->whereHas('tags', fn ($q) => $q->where('slug', $this->tag)))
            ->with(['category'])
            ->when($this->sort === 'newest', fn ($q) => $q->latest())
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->paginate(24);
    }

    #[Computed]
    public function creators()
    {
        return User::query()
            ->where('is_active', true)
            ->where('is_admin', false)
            ->whereHas('products', fn ($q) => $q->where('is_active', true))
            ->when($this->search, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($this->search).'%']))
            ->when($this->creatorCategory, fn ($q) => $q->whereHas('products', fn ($q) => $q
                ->where('is_active', true)
                ->where('category_id', $this->creatorCategory)
            ))
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->when($this->creatorSort === 'popular', fn ($q) => $q->orderByDesc('products_count'))
            ->when($this->creatorSort === 'newest', fn ($q) => $q->latest())
            ->when($this->creatorSort === 'name', fn ($q) => $q->orderBy('name'))
            ->paginate(24);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function activeTag()
    {
        return $this->tag ? Tag::where('slug', $this->tag)->first() : null;
    }
};
?>

<div>
    <flux:main class="!p-0">
        {{-- Tabs --}}
        <div class="border-b border-zinc-800 px-6">
            <div class="flex gap-6">
                <button
                    wire:click="$set('tab', 'products')"
                    class="border-b-2 py-4 text-sm font-medium transition-colors {{ $tab === 'products' ? 'border-white text-white' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}"
                >
                    Products
                </button>
                <button
                    wire:click="$set('tab', 'creators')"
                    class="border-b-2 py-4 text-sm font-medium transition-colors {{ $tab === 'creators' ? 'border-white text-white' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}"
                >
                    Creators
                </button>
            </div>
        </div>

        <div class="flex min-h-screen">
            {{-- Sidebar --}}
            <aside class="hidden w-52 shrink-0 border-e border-zinc-800 px-4 py-6 lg:block xl:w-60">
                @if ($tab === 'products')
                    <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-zinc-500">Filter results</p>

                    <div class="mb-6">
                        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search..." size="sm" icon="magnifying-glass" clearable />
                    </div>

                    <div class="mb-6">
                        <p class="mb-2 text-xs font-medium text-zinc-400">Price</p>
                        <ul class="space-y-1">
                            @foreach (['' => 'All', 'free' => 'Free', 'paid' => 'Paid'] as $value => $label)
                                <li>
                                    <button wire:click="$set('price', '{{ $value }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $price === $value ? 'text-white' : 'text-zinc-400' }}">
                                        {{ $label }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

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

                @else
                    <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-zinc-500">Filter creators</p>

                    <div class="mb-6">
                        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search..." size="sm" icon="magnifying-glass" clearable />
                    </div>

                    <div class="mb-6">
                        <p class="mb-2 text-xs font-medium text-zinc-400">Category</p>
                        <ul class="space-y-1">
                            <li>
                                <button wire:click="$set('creatorCategory', '')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $creatorCategory === '' ? 'text-white' : 'text-zinc-400' }}">
                                    All categories
                                </button>
                            </li>
                            @foreach ($this->categories as $cat)
                                <li>
                                    <button wire:key="{{ $cat->id }}" wire:click="$set('creatorCategory', '{{ $cat->id }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $creatorCategory === $cat->id ? 'text-white' : 'text-zinc-400' }}">
                                        {{ $cat->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-medium text-zinc-400">Sort by</p>
                        <ul class="space-y-1">
                            @foreach (['popular' => 'Most products', 'newest' => 'Newest', 'name' => 'Alphabetically'] as $value => $label)
                                <li>
                                    <button wire:click="$set('creatorSort', '{{ $value }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $creatorSort === $value ? 'text-white' : 'text-zinc-400' }}">
                                        {{ $label }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>

            {{-- Main content --}}
            <div class="flex-1 px-6 py-6">

                @if ($tab === 'products')
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm text-zinc-500">{{ $this->products->total() }} results</p>

                        <div class="flex gap-2 lg:hidden">
                            <flux:select wire:model.live="category" size="sm" placeholder="Category">
                                <flux:select.option value="">All categories</flux:select.option>
                                @foreach ($this->categories as $cat)
                                    <flux:select.option wire:key="{{ $cat->id }}" value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    @if ($this->activeTag)
                        <div class="mb-3 flex items-center gap-2">
                            <span class="text-xs text-zinc-500">Filtered by tag:</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-800 px-2.5 py-1 text-xs text-zinc-300">
                                #{{ $this->activeTag->name }}
                                <button wire:click="$set('tag', '')" class="text-zinc-600 hover:text-white">✕</button>
                            </span>
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                        @forelse ($this->products as $product)
                            <a
                                wire:key="{{ $product->id }}"
                                href="{{ route('products.show', $product->slug) }}"
                                wire:navigate
                                class="group block rounded-lg border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700 hover:bg-zinc-800/60"
                            >
                                <div class="aspect-[4/3] w-full overflow-hidden rounded-t-lg bg-zinc-800">
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

                    <div class="mt-8">{{ $this->products->links() }}</div>

                @else
                    {{-- Creators tab --}}
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-sm text-zinc-500">{{ $this->creators->total() }} creators</p>
                        @if ($creatorCategory)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-800 px-2.5 py-1 text-xs text-zinc-300">
                                {{ $this->categories->firstWhere('id', $creatorCategory)?->name }}
                                <button wire:click="$set('creatorCategory', '')" class="text-zinc-600 hover:text-white">✕</button>
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse ($this->creators as $creator)
                            <a
                                wire:key="{{ $creator->id }}"
                                href="{{ route('users.show', $creator->username) }}"
                                wire:navigate
                                class="group overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900 transition hover:border-zinc-700"
                            >
                                {{-- Background strip --}}
                                <div @class([
                                    'h-16 w-full bg-gradient-to-br',
                                    'from-zinc-700 to-zinc-900' => ($creator->profile_background ?? 'default') === 'default',
                                    'from-violet-800 to-violet-950' => $creator->profile_background === 'violet',
                                    'from-blue-800 to-blue-950' => $creator->profile_background === 'blue',
                                    'from-emerald-800 to-emerald-950' => $creator->profile_background === 'emerald',
                                    'from-rose-800 to-rose-950' => $creator->profile_background === 'rose',
                                    'from-amber-800 to-amber-950' => $creator->profile_background === 'amber',
                                ])
                                @if ($creator->profileBannerUrl())
                                    style="background-image: url('{{ $creator->profileBannerUrl() }}'); background-size: cover; background-position: center;"
                                @endif
                                ></div>

                                <div class="px-4 pb-4">
                                    {{-- Avatar overlapping strip --}}
                                    <div class="-mt-8 mb-3">
                                        <div class="h-16 w-16 overflow-hidden rounded-full border-2 border-zinc-900 bg-zinc-700">
                                            @if ($creator->avatarUrl())
                                                <img src="{{ $creator->avatarUrl() }}" alt="{{ $creator->name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center text-xl font-bold text-white">
                                                    {{ $creator->initials() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <p class="truncate font-semibold text-zinc-200 group-hover:text-white">{{ $creator->name }}</p>
                                    <p class="truncate text-xs text-zinc-500">{{ '@'.$creator->username }}</p>
                                    <p class="mt-2 text-xs font-medium text-zinc-400">{{ $creator->products_count }} {{ $creator->products_count === 1 ? 'product' : 'products' }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full py-20 text-center">
                                <p class="text-zinc-500">No creators found.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">{{ $this->creators->links() }}</div>
                @endif

            </div>
        </div>
    </flux:main>
</div>
