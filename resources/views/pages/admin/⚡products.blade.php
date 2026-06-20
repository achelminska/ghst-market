<?php

use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function toggleActive(string $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => ! $product->is_active]);
        unset($this->products);
    }

    #[Computed]
    public function products()
    {
        return Product::with('user', 'category')
            ->withCount('purchases')
            ->when($this->search, fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($this->search).'%']))
            ->latest()
            ->paginate(20);
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-white">Products</h1>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search..." icon="magnifying-glass" clearable size="sm" class="w-56" />
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-800">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-800 bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Seller</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Price</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Sales</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-widest text-zinc-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 bg-zinc-950">
                @forelse ($this->products as $product)
                    <tr wire:key="{{ $product->id }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-12 shrink-0 overflow-hidden rounded bg-zinc-800">
                                    @if ($product->thumbnail)
                                        <img src="{{ $product->thumbnail }}" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-200">{{ $product->title }}</p>
                                    <p class="text-xs text-zinc-600">{{ $product->category?->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-400">{{ $product->user->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-white">
                            {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                        </td>
                        <td class="px-4 py-3 text-right text-zinc-400">{{ $product->purchases_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive('{{ $product->id }}')" class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium transition
                                {{ $product->is_active ? 'bg-emerald-500/10 text-emerald-400 hover:bg-red-500/10 hover:text-red-400' : 'bg-zinc-800 text-zinc-500 hover:bg-emerald-500/10 hover:text-emerald-400' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-zinc-600">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $this->products->links() }}</div>
</div>
