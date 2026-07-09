@props([
    'title',
    'description',
])

<div class="flex w-full flex-col items-center text-center">
    <flux:heading size="xl">{{ $title }}</flux:heading>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
