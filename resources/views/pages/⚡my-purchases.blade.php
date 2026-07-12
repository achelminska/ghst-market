<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.guest')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function purchases()
    {
        return Auth::user()
            ->purchases()
            ->with('product.category')
            ->when($this->search, fn ($q) => $q->whereHas(
                'product',
                fn ($q) => $q->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($this->search).'%'])
            ))
            ->latest('purchased_at')
            ->paginate(20);
    }
};
?>

<div>
    <flux:main>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">My purchases</h1>
                <p class="mt-1 text-sm text-zinc-500">All your purchased digital assets.</p>
            </div>
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search..."
                icon="magnifying-glass"
                clearable
                class="w-full sm:w-56"
            />
        </div>

        @if ($this->purchases->isEmpty() && !$search)
            <div class="flex flex-col items-center rounded-2xl border border-dashed border-zinc-800 bg-zinc-900/50 py-20 text-center">
                <img src="{{ asset('images/ghst-icon.png') }}" alt="" class="mb-4 h-16 w-auto opacity-30 mix-blend-screen">
                <p class="text-sm text-zinc-500">You haven't purchased anything yet.</p>
                <a href="{{ route('products.index') }}" wire:navigate class="mt-4 rounded-xl bg-accent px-5 py-2 text-sm font-semibold text-accent-foreground transition-colors hover:bg-green-300">
                    Browse products
                </a>
            </div>
        @else
            {{-- Mobile: cards --}}
            <div class="space-y-3 sm:hidden">
                @forelse ($this->purchases as $purchase)
                    <div wire:key="m-{{ $purchase->id }}" class="rounded-xl border border-zinc-800 bg-zinc-900 p-4">
                        <div class="flex items-start gap-3">
                            <div class="h-14 w-20 shrink-0 overflow-hidden rounded-lg bg-zinc-800">
                                @if ($purchase->product->thumbnail)
                                    <img src="{{ $purchase->product->thumbnail }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('products.show', $purchase->product->slug) }}" wire:navigate class="font-medium text-zinc-200 hover:text-white">
                                    {{ $purchase->product->title }}
                                </a>
                                <p class="mt-1 text-xs text-zinc-500">{{ $purchase->purchased_at->format('d M Y') }}</p>
                                @if ($purchase->product->category)
                                    <p class="mt-0.5 text-xs text-zinc-600">{{ $purchase->product->category->name }}</p>
                                @endif
                            </div>
                            <p class="shrink-0 text-sm font-semibold {{ $purchase->price_paid > 0 ? 'text-white' : 'text-accent' }}">
                                {{ $purchase->price_paid > 0 ? '$'.number_format($purchase->price_paid, 2) : 'Free' }}
                            </p>
                        </div>
                        <a
                            href="{{ route('purchases.download', $purchase) }}"
                            class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-lg border border-zinc-700 py-2 text-sm font-medium text-zinc-300 transition hover:border-accent/40 hover:text-accent"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download
                        </a>
                    </div>
                @empty
                    <div class="rounded-xl border border-zinc-800 bg-zinc-900 py-12 text-center text-sm text-zinc-500">
                        No results for "{{ $search }}"
                    </div>
                @endforelse
            </div>

            {{-- Desktop: table --}}
            <div class="hidden overflow-hidden rounded-2xl border border-zinc-800 sm:block">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-800 bg-zinc-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Product</th>
                            <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500 sm:table-cell">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Paid</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800 bg-zinc-950">
                        @forelse ($this->purchases as $purchase)
                            <tr wire:key="{{ $purchase->id }}" class="transition hover:bg-zinc-900/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-14 shrink-0 overflow-hidden rounded-lg bg-zinc-800">
                                            @if ($purchase->product->thumbnail)
                                                <img src="{{ $purchase->product->thumbnail }}" alt="" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <a href="{{ route('products.show', $purchase->product->slug) }}" wire:navigate class="font-medium text-zinc-200 hover:text-white">
                                            {{ $purchase->product->title }}
                                        </a>
                                    </div>
                                </td>
                                <td class="hidden px-4 py-3 text-zinc-500 sm:table-cell">
                                    {{ $purchase->product->category?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-zinc-500">
                                    {{ $purchase->purchased_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-white">
                                    {{ $purchase->price_paid > 0 ? '$'.number_format($purchase->price_paid, 2) : 'Free' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a
                                        href="{{ route('purchases.download', $purchase) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-700 px-3 py-1.5 text-xs font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-500">No results for "{{ $search }}"</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $this->purchases->links() }}
            </div>
        @endif
    </flux:main>
</div>
