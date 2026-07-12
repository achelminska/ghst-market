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
            <div class="flex h-16 items-center justify-between px-4 sm:px-6">
                <a href="{{ route('home') }}" wire:navigate class="group flex items-center gap-2.5">
                    <img src="{{ asset('images/ghst-icon.png') }}" alt="" class="h-10 w-auto mix-blend-screen transition-transform duration-200 group-hover:scale-110">
                    <span class="font-display text-base tracking-tight text-white">GHST MARKET</span>
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
                            <a href="{{ route('register') }}" wire:navigate class="rounded-lg bg-accent px-4 py-1.5 text-sm font-semibold text-accent-foreground transition-colors duration-200 hover:bg-green-300">
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

        <footer class="mt-16 border-t border-zinc-800/60 py-12">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-10 sm:grid-cols-3">
                    {{-- Brand --}}
                    <div>
                        <img src="{{ asset('images/ghst-icon.png') }}" alt="GHST Market" class="mb-3 h-12 w-auto mix-blend-screen">
                        <p class="text-sm leading-relaxed text-zinc-500">
                            A marketplace for digital creative assets — fonts, templates, illustrations, music and indie game files.
                        </p>
                    </div>

                    {{-- Explore --}}
                    <div>
                        <p class="mb-3 font-display text-xs uppercase tracking-widest text-zinc-400">Explore</p>
                        <ul class="space-y-2 text-sm">
                            <li><a href="{{ route('products.index') }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Browse products</a></li>
                            <li><a href="{{ route('products.index', ['tab' => 'creators']) }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Creators</a></li>
                            <li><a href="{{ route('home') }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Home</a></li>
                        </ul>
                    </div>

                    {{-- Account --}}
                    <div>
                        <p class="mb-3 font-display text-xs uppercase tracking-widest text-zinc-400">Account</p>
                        <ul class="space-y-2 text-sm">
                            @auth
                                <li><a href="{{ route('dashboard') }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Dashboard</a></li>
                                <li><a href="{{ route('my-products.index') }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Sell your work</a></li>
                                <li><a href="{{ route('wallet') }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Wallet</a></li>
                            @else
                                <li><a href="{{ route('login') }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Log in</a></li>
                                @if (Route::has('register'))
                                    <li><a href="{{ route('register') }}" wire:navigate class="text-zinc-500 transition-colors hover:text-accent">Create an account</a></li>
                                @endif
                            @endauth
                        </ul>
                    </div>
                </div>

                <div class="mt-10 flex flex-col items-center justify-between gap-3 border-t border-zinc-800/60 pt-6 text-xs text-zinc-600 sm:flex-row">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <p class="flex items-center gap-1.5">
                        <svg class="size-3.5 text-accent" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" /></svg>
                        Secure downloads &middot; Instant access
                    </p>
                </div>
            </div>
        </footer>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
