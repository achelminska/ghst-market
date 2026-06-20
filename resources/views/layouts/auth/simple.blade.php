<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ filled($title ?? null) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <link rel="icon" href="/favicon.ico" sizes="any">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">

            <a href="{{ route('home') }}" wire:navigate class="mb-8 text-xl font-semibold tracking-tight text-white transition-opacity hover:opacity-70">
                {{ config('app.name') }}
            </a>

            <div class="w-full max-w-sm rounded-2xl border border-zinc-800 bg-zinc-900 p-8">
                {{ $slot }}
            </div>

            <a href="{{ route('products.index') }}" wire:navigate class="mt-6 text-sm text-zinc-600 transition-colors hover:text-zinc-400">
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
