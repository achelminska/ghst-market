<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ filled($title ?? null) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <header class="sticky top-0 z-50 border-b border-zinc-800/60 bg-zinc-950/80 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center">
                    <img src="{{ asset('images/ghst-market-logo.png') }}" alt="GHST Market" class="hidden h-8 w-auto mix-blend-screen sm:block">
                    <img src="{{ asset('images/ghst-icon.png') }}" alt="GHST Market" class="block h-14 w-auto mix-blend-screen sm:hidden">
                </a>

                <nav class="flex items-center gap-4">
                    <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-zinc-400 transition-colors hover:text-white">
                        Browse
                    </a>

                    @auth
                        <a href="{{ route('wallet') }}" wire:navigate class="hidden text-sm text-zinc-400 transition-colors hover:text-white sm:block">
                            ${{ number_format(auth()->user()?->balance ?? 0, 2) }}
                        </a>
                        <x-desktop-user-menu />
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="text-sm text-zinc-400 transition-colors hover:text-white">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" wire:navigate class="rounded-lg bg-white px-4 py-1.5 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-100">
                                Sign up
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-16 border-t border-zinc-800/60 py-8">
            <div class="mx-auto max-w-7xl px-6 text-center text-sm text-zinc-600">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
