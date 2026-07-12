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

new #[Layout('layouts.guest')] class extends Component
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

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'price', 'tag');
        $this->resetPage();
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
        return Category::orderBy('name')
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->get();
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
                    class="border-b-2 py-4 text-sm font-medium transition-colors {{ $tab === 'products' ? 'border-accent text-white' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}"
                >
                    Products
                </button>
                <button
                    wire:click="$set('tab', 'creators')"
                    class="border-b-2 py-4 text-sm font-medium transition-colors {{ $tab === 'creators' ? 'border-accent text-white' : 'border-transparent text-zinc-500 hover:text-zinc-300' }}"
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
                                    <button wire:click="$set('price', '{{ $value }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $price === $value ? 'bg-zinc-800/70 font-medium text-accent' : 'text-zinc-400' }}">
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
                                <button wire:click="$set('category', '')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $category === '' ? 'bg-zinc-800/70 font-medium text-accent' : 'text-zinc-400' }}">
                                    All categories
                                </button>
                            </li>
                            @foreach ($this->categories as $cat)
                                <li>
                                    <button wire:key="{{ $cat->id }}" wire:click="$set('category', '{{ $cat->id }}')" class="flex w-full items-center justify-between gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $category === $cat->id ? 'bg-zinc-800/70 font-medium text-accent' : 'text-zinc-400' }}">
                                        <span class="truncate">{{ $cat->name }}</span>
                                        <span class="text-xs {{ $category === $cat->id ? 'text-accent/70' : 'text-zinc-600' }}">{{ $cat->products_count }}</span>
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
                                    <button wire:click="$set('sort', '{{ $value }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $sort === $value ? 'bg-zinc-800/70 font-medium text-accent' : 'text-zinc-400' }}">
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
                                <button wire:click="$set('creatorCategory', '')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $creatorCategory === '' ? 'bg-zinc-800/70 font-medium text-accent' : 'text-zinc-400' }}">
                                    All categories
                                </button>
                            </li>
                            @foreach ($this->categories as $cat)
                                <li>
                                    <button wire:key="{{ $cat->id }}" wire:click="$set('creatorCategory', '{{ $cat->id }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $creatorCategory === $cat->id ? 'bg-zinc-800/70 font-medium text-accent' : 'text-zinc-400' }}">
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
                                    <button wire:click="$set('creatorSort', '{{ $value }}')" class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm transition-colors hover:bg-zinc-800 {{ $creatorSort === $value ? 'bg-zinc-800/70 font-medium text-accent' : 'text-zinc-400' }}">
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

                    {{-- Skeleton grid while filtering/searching --}}
                    <div wire:loading.grid wire:target="search, category, price, sort, tag" class="hidden grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                        @foreach (range(1, 8) as $i)
                            <div class="animate-pulse overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900">
                                <div class="aspect-[4/3] w-full bg-zinc-800"></div>
                                <div class="space-y-2 p-4">
                                    <div class="h-4 w-3/4 rounded bg-zinc-800"></div>
                                    <div class="h-3 w-1/3 rounded bg-zinc-800"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div wire:loading.remove wire:target="search, category, price, sort, tag" class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                        @forelse ($this->products as $product)
                            <x-product-card :product="$product" wire:key="{{ $product->id }}" />
                        @empty
                            <div class="col-span-full flex flex-col items-center py-20 text-center">
                                <img src="{{ asset('images/ghst-icon.png') }}" alt="" class="mb-4 h-16 w-auto opacity-30 mix-blend-screen">
                                <p class="font-display text-sm text-zinc-400">Nothing here but ghosts</p>
                                <p class="mt-2 text-sm text-zinc-500">No products match your filters.</p>
                                <button
                                    wire:click="clearFilters"
                                    class="mt-6 rounded-lg border border-zinc-700 px-4 py-2 text-sm text-zinc-300 transition-colors hover:border-accent/50 hover:text-accent"
                                >
                                    Clear all filters
                                </button>
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
                                class="group overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900 transition duration-200 hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-lg hover:shadow-accent/10"
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
                            <div class="col-span-full flex flex-col items-center py-20 text-center">
                                <img src="{{ asset('images/ghst-icon.png') }}" alt="" class="mb-4 h-16 w-auto opacity-30 mix-blend-screen">
                                <p class="font-display text-sm text-zinc-400">Nothing here but ghosts</p>
                                <p class="mt-2 text-sm text-zinc-500">No creators match your filters.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">{{ $this->creators->links() }}</div>
                @endif

            </div>
        </div>
    </flux:main>
</div>
