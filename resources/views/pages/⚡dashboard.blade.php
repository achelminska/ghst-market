<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function purchases()
    {
        return auth()->user()->purchases()->with('product')->get();
    }
};
?>

<div>
    <flux:main>
        <flux:heading size="xl" class="mb-6">My Purchases</flux:heading>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->purchases as $purchase)
                <div wire:key="{{ $purchase->id }}" class="flex flex-col rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-1 flex-col p-4">
                        <flux:heading size="sm" class="mb-1">{{ $purchase->product->title }}</flux:heading>
                        <flux:text class="text-sm">Purchased {{ $purchase->purchased_at->format('d M Y') }}</flux:text>
                        <flux:text class="mt-1 text-sm">
                            {{ $purchase->price_paid > 0 ? '$'.number_format($purchase->price_paid, 2) : 'Free' }}
                        </flux:text>
                    </div>
                    <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                        <flux:button
                            size="sm"
                            variant="primary"
                            icon="arrow-down-tray"
                            :href="route('purchases.download', $purchase)"
                            class="w-full"
                        >
                            Download
                        </flux:button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center">
                    <flux:text>No purchases yet.</flux:text>
                    <div class="mt-4">
                        <flux:button :href="route('products.index')" variant="primary" wire:navigate>
                            Browse products
                        </flux:button>
                    </div>
                </div>
            @endforelse
        </div>
    </flux:main>
</div>