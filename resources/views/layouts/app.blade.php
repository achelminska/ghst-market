<x-layouts::guest :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::guest>
