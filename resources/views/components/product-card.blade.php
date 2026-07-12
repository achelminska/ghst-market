@props(['product'])

<a
    href="{{ route('products.show', $product->slug) }}"
    wire:navigate
    {{ $attributes->merge(['class' => 'group block overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition duration-200 hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-lg hover:shadow-accent/10']) }}
>
    <div class="aspect-[4/3] w-full overflow-hidden bg-zinc-800">
        @if ($product->thumbnail)
            <img src="{{ $product->thumbnail }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center">
                <img src="{{ asset('images/ghst-icon.png') }}" alt="" class="h-12 w-auto opacity-25 mix-blend-screen transition duration-300 group-hover:scale-110 group-hover:opacity-40">
            </div>
        @endif
    </div>

    <div class="p-4">
        <p class="truncate text-sm font-medium text-zinc-200 transition-colors group-hover:text-white">
            {{ $product->title }}
        </p>

        <div class="mt-2 flex items-center justify-between gap-2">
            <span class="text-sm font-semibold {{ $product->price > 0 ? 'text-white' : 'text-accent' }}">
                {{ $product->price > 0 ? '$'.number_format($product->price, 2) : 'Free' }}
            </span>

            @if ($product->category)
                <span class="truncate rounded-full bg-zinc-800 px-2 py-0.5 text-xs text-zinc-400">
                    {{ $product->category->name }}
                </span>
            @endif
        </div>
    </div>
</a>
