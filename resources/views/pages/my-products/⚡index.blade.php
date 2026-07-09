<?php

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public ?string $confirmingDelete = null;

    public function deleteProduct(string $id): void
    {
        $product = Product::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $product->delete();

        $this->confirmingDelete = null;
        unset($this->products);
    }

    #[Computed]
    public function products()
    {
        return Product::where('user_id', Auth::id())
            ->with('category')
            ->withCount('purchases')
            ->latest()
            ->get();
    }
};
?>

<div>
    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">My products</h1>
                <p class="mt-1 text-sm text-zinc-500">Products you've listed for sale.</p>
            </div>
            <a
                href="{{ route('my-products.create') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-100"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New product
            </a>
        </div>

        @if ($this->products->isEmpty())
            <div class="rounded-2xl border border-dashed border-zinc-800 py-20 text-center">
                <p class="text-zinc-500">You haven't listed any products yet.</p>
                <a href="{{ route('my-products.create') }}" wire:navigate class="mt-4 inline-block rounded-xl bg-white px-5 py-2 text-sm font-semibold text-zinc-900 hover:bg-zinc-100">
                    Create your first product
                </a>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl border border-zinc-800">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-800 bg-zinc-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Product</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500 sm:table-cell">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Sales</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-widest text-zinc-500">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800 bg-zinc-950">
                        @foreach ($this->products as $product)
                            <tr wire:key="{{ $product->id }}" class="transition hover:bg-zinc-900/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-zinc-800">
                                            @if ($product->thumbnail)
                                                <img src="{{ $product->thumbnail }}" alt="" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="font-medium text-zinc-200 hover:text-white">
                                            {{ $product->title }}
                                        </a>
                                    </div>
                                </td>
                                <td class="hidden px-4 py-3 text-zinc-500 sm:table-cell">
                                    {{ $product->category?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-white">
                                    {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                                </td>
                                <td class="px-4 py-3 text-right text-zinc-400">
                                    {{ $product->purchases_count }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($product->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-400">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-zinc-800 px-2 py-0.5 text-xs font-medium text-zinc-500">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('my-products.edit', $product->slug) }}"
                                            wire:navigate
                                            class="rounded-lg border border-zinc-700 px-3 py-1.5 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white"
                                        >
                                            Edit
                                        </a>

                                        @if ($confirmingDelete === $product->id)
                                            <span class="text-xs text-zinc-500">Sure?</span>
                                            <button wire:click="deleteProduct('{{ $product->id }}')" class="rounded-lg border border-red-800 px-3 py-1.5 text-xs font-medium text-red-400 transition hover:border-red-600 hover:text-red-300">
                                                Yes, delete
                                            </button>
                                            <button wire:click="$set('confirmingDelete', null)" class="text-xs text-zinc-600 hover:text-zinc-400">
                                                Cancel
                                            </button>
                                        @else
                                            <button wire:click="$set('confirmingDelete', '{{ $product->id }}')" class="rounded-lg border border-zinc-800 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:border-red-800 hover:text-red-400">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </flux:main>
</div>
