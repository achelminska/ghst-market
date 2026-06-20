<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search, fn($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($this->search).'%']))
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->with(['category', 'tags'])
            ->paginate(12);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

};
?>

<div>
    <flux:main>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="xl">Products</flux:heading>

            <div class="flex gap-3">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search products..."
                    icon="magnifying-glass"
                    clearable
                />

                <flux:select wire:model.live="category" placeholder="All categories">
                    <flux:select.option value="">All categories</flux:select.option>
                    @foreach ($this->categories as $cat)
                        <flux:select.option wire:key="{{ $cat->id }}" value="{{ $cat->id }}">
                            {{ $cat->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @forelse ($this->products as $product)
                <div wire:key="{{ $product->id }}" class="flex flex-col rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="aspect-video w-full overflow-hidden rounded-t-xl bg-zinc-100 dark:bg-zinc-800">
                        @if ($product->thumbnail)
                            <img src="{{ $product->thumbnail }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center">
                                <svg class="size-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 16.5V19a.75.75 0 00.75.75h16.5A.75.75 0 0021 19v-2.5M3 16.5V7.5A.75.75 0 013.75 6.75h16.5A.75.75 0 0121 7.5v9" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <flux:badge color="zinc" size="sm" class="mb-2 self-start">{{ $product->category->name }}</flux:badge>
                        <flux:heading size="sm" class="mb-1">{{ $product->title }}</flux:heading>
                        <flux:text class="line-clamp-2 flex-1 text-sm">{{ $product->description }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                        <flux:heading size="sm">
                            {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                        </flux:heading>
                        <flux:button size="sm" variant="primary" :href="route('products.show', $product->slug)" wire:navigate>View</flux:button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center">
                    <flux:text>No products found.</flux:text>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $this->products->links() }}
        </div>
    </flux:main>
</div>