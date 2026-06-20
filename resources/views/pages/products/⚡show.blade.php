<?php

use Livewire\Component;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Exceptions\AlreadyPurchasedException;
use App\Exceptions\InsufficientBalanceException;
use App\Services\PurchaseService;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app'), Title('Product')] class extends Component
{
    public Product $product;
    public string $errorMessage = '';

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'tags', 'user'])
            ->firstOrFail();
    }

    #[Computed]
    public function alreadyPurchased(): bool
    {
        return Auth::check() && Auth::user()->purchases()
            ->where('product_id', $this->product->id)
            ->exists();
    }

    public function purchase(PurchaseService $service): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        try {
            $service->purchase(Auth::user(), $this->product);
            unset($this->alreadyPurchased);
        } catch (AlreadyPurchasedException) {
            $this->errorMessage = 'You already own this product.';
        } catch (InsufficientBalanceException) {
            $this->errorMessage = 'Insufficient balance.';
        }
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

        {{-- Cover image --}}
        @if ($this->product->cover_image)
            <div class="mb-8 overflow-hidden rounded-2xl">
                <img
                    src="{{ $this->product->cover_image }}"
                    alt="{{ $this->product->title }}"
                    class="h-72 w-full object-cover lg:h-96"
                >
            </div>
        @endif

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

                    @if ($this->alreadyPurchased)
                        <flux:button variant="ghost" class="w-full" disabled>
                            Already purchased
                        </flux:button>
                    @else
                        <flux:button variant="primary" class="w-full" wire:click="purchase">
                            {{ $this->product->price > 0 ? 'Buy now' : 'Get for free' }}
                        </flux:button>
                    @endif

                    @if ($errorMessage)
                        <flux:text class="mt-2 text-sm text-red-500">{{ $errorMessage }}</flux:text>
                    @endif
                </div>
            </div>
        </div>
    </flux:main>
</div>