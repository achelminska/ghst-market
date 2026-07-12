@props([
    'wordmark' => true,
    'iconClass' => 'size-6',
    'boxClass' => 'size-9',
])

<a {{ $attributes->merge(['class' => 'group flex items-center gap-2.5']) }}>
    <span @class([
        'flex shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-zinc-700/90 via-zinc-800 to-emerald-950/70 ring-1 ring-white/10 transition duration-200 group-hover:ring-accent/40',
        $boxClass,
    ])>
        <img
            src="{{ asset('images/ghst-icon.png') }}"
            alt=""
            @class([$iconClass, 'mix-blend-screen transition-transform duration-200 group-hover:scale-110'])
        >
    </span>

    @if ($wordmark)
        <span class="hidden font-display text-sm uppercase leading-none tracking-tight sm:block">
            <span class="text-white">GHST </span>
            <span class="bg-gradient-to-r from-emerald-200 to-accent bg-clip-text text-transparent">MARKET</span>
        </span>
    @endif
</a>
