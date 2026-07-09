@props([
    'sidebar' => false,
])

<img src="{{ asset('images/ghst-market-logo.png') }}" alt="GHST Market" {{ $attributes->merge(['class' => 'h-7 w-auto']) }}>
