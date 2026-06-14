<?php

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new #[Layout('layouts.app'), Title('Product')] class extends Component
{
    public Product $product;

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'tags', 'user'])
            ->firstOrFail();
    }
};
?>

<div>
    <flux:main>
        <div class="mb-4">
            <flux:button :href="route('products.index')" variant="ghost" icon="arrow-left" wire:navigate>
                Back to products
            </flux:button>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="mb-2 flex flex-wrap gap-2">
                    <flux:badge color="zinc">{{ $this->product->category->name }}</flux:badge>
                    @foreach ($this->product->tags as $tag)
                        <flux:badge wire:key="{{ $tag->id }}" color="lime" size="sm">{{ $tag->name }}</flux:badge>
                    @endforeach
                </div>

                <flux:heading size="xl" class="mb-4">{{ $this->product->title }}</flux:heading>
                <flux:text class="whitespace-pre-line">{{ $this->product->description }}</flux:text>
            </div>

            <div>
                <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                    <flux:heading size="xl" class="mb-1">
                        {{ $this->product->price > 0 ? '$'.number_format($this->product->price, 2) : 'Free' }}
                    </flux:heading>
                    <flux:text class="mb-4 text-sm">by {{ $this->product->user->name }}</flux:text>

                    <flux:button variant="primary" class="w-full">
                        {{ $this->product->price > 0 ? 'Buy now' : 'Download for free' }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:main>
</div>