@props([
    'imageUrl' => null,
    'colorPreset' => 'default',
])

<div
    @class([
        'min-h-screen w-full',
        'bg-gradient-to-br from-zinc-900 to-zinc-950' => ! $imageUrl && $colorPreset === 'default',
        'bg-gradient-to-br from-violet-950 via-zinc-900 to-zinc-950' => ! $imageUrl && $colorPreset === 'violet',
        'bg-gradient-to-br from-blue-950 via-zinc-900 to-zinc-950' => ! $imageUrl && $colorPreset === 'blue',
        'bg-gradient-to-br from-emerald-950 via-zinc-900 to-zinc-950' => ! $imageUrl && $colorPreset === 'emerald',
        'bg-gradient-to-br from-rose-950 via-zinc-900 to-zinc-950' => ! $imageUrl && $colorPreset === 'rose',
        'bg-gradient-to-br from-amber-950 via-zinc-900 to-zinc-950' => ! $imageUrl && $colorPreset === 'amber',
    ])
    @if ($imageUrl)
        style="background-image: url('{{ $imageUrl }}'); background-repeat: repeat; background-size: auto; background-position: top center;"
    @endif
>
    <div class="mx-auto min-h-screen max-w-4xl border-x border-zinc-800/50 bg-zinc-950/95 shadow-2xl backdrop-blur-sm">
        {{ $slot }}
    </div>
</div>
