<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public User $seller;

    public function mount(string $username): void
    {
        $this->seller = User::where('username', $username)->firstOrFail();
    }

    #[Computed]
    #[Title('')]
    public function title(): string
    {
        return $this->seller->name;
    }

    #[Computed]
    public function products()
    {
        return $this->seller->products()
            ->where('is_active', true)
            ->with('category')
            ->withCount('purchases')
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'products' => $this->seller->products()->where('is_active', true)->count(),
            'sales'    => $this->seller->products()->withCount('purchases')->get()->sum('purchases_count'),
            'since'    => $this->seller->created_at->format('M Y'),
        ];
    }
};
?>

<x-page-background
    :image-url="$this->seller->profileBannerUrl()"
    :color-preset="$this->seller->profile_background ?? 'default'"
>
    {{-- Profile header --}}
    <div class="border-b border-zinc-800 px-6 py-8">
        <div class="flex items-center gap-5">
            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full border-2 border-zinc-700 bg-zinc-800">
                @if ($this->seller->avatarUrl())
                    <img src="{{ $this->seller->avatarUrl() }}" alt="" class="block h-20 w-20 object-cover">
                @else
                    <div class="flex h-20 w-20 items-center justify-center text-2xl font-bold text-white">
                        {{ $this->seller->initials() }}
                    </div>
                @endif
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $this->seller->name }}</h1>
                <p class="mt-0.5 text-sm text-zinc-400">{{ '@'.$this->seller->username }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-zinc-500">
                    <span><span class="font-semibold text-zinc-300">{{ $this->stats['products'] }}</span> products</span>
                    <span><span class="font-semibold text-zinc-300">{{ $this->stats['sales'] }}</span> sales</span>
                    <span>Member since {{ $this->stats['since'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Products --}}
    <div class="px-6 py-8">
        @if ($this->products->isEmpty())
            <div class="py-16 text-center text-zinc-600">This creator has no public products yet.</div>
        @else
            <h2 class="mb-6 text-sm font-medium uppercase tracking-widest text-zinc-500">Products</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($this->products as $product)
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
                                    <svg class="size-8 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-2.5">
                            <p class="truncate text-xs font-medium text-zinc-300 group-hover:text-white">{{ $product->title }}</p>
                            <div class="mt-0.5 flex items-center justify-between">
                                <span class="text-xs font-semibold text-white">
                                    {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
                                </span>
                                <span class="text-xs text-zinc-600">{{ $product->purchases_count }} sales</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $this->products->links() }}</div>
        @endif
    </div>
</x-page-background>
