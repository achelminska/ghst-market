<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content" />
        <title>{{ filled($title ?? null) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-dvh bg-zinc-950 text-zinc-100 antialiased">
        <div class="flex min-h-dvh flex-col items-center justify-center px-6 py-4 sm:py-12">

            <a href="{{ route('home') }}" wire:navigate class="mb-4 transition-opacity sm:mb-8">
                <img src="{{ asset('images/ghst-market-logo.png') }}" alt="GHST Market" class="hidden h-40 w-auto mix-blend-screen sm:block">
                <img src="{{ asset('images/ghst-icon.png') }}" alt="GHST Market" class="block h-20 w-auto mix-blend-screen sm:hidden">
            </a>

            <div class="w-full max-w-sm rounded-2xl border border-zinc-800 bg-zinc-900 p-6 sm:p-8">
                {{ $slot }}
            </div>

            <a href="{{ route('products.index') }}" wire:navigate class="mt-4 text-sm text-zinc-600 transition-colors hover:text-zinc-400 sm:mt-6">
                ← Browse without an account
            </a>

        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
