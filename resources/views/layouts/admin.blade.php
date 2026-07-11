<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin — {{ filled($title ?? null) ? $title.' · ' : '' }}{{ config('app.name') }}</title>
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <div class="flex min-h-screen">
            {{-- Sidebar --}}
            <aside class="hidden w-56 shrink-0 flex-col border-e border-zinc-800 bg-zinc-900 lg:flex">
                <div class="border-b border-zinc-800 px-5 py-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-white">
                        {{ config('app.name') }}
                        <span class="ml-1.5 rounded bg-zinc-700 px-1.5 py-0.5 text-xs text-zinc-400">admin</span>
                    </a>
                </div>

                <nav class="flex flex-1 flex-col gap-1 p-3">
                    @php
                        $links = [
                            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'squares'],
                            ['route' => 'admin.users',     'label' => 'Users',     'icon' => 'users'],
                            ['route' => 'admin.products',  'label' => 'Products',  'icon' => 'cube'],
                            ['route' => 'admin.categories','label' => 'Categories','icon' => 'tag'],
                            ['route' => 'admin.tags',      'label' => 'Tags',      'icon' => 'hashtag'],
                        ];
                    @endphp

                    @foreach ($links as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition-colors
                                {{ request()->routeIs($link['route'])
                                    ? 'bg-zinc-800 text-white'
                                    : 'text-zinc-400 hover:bg-zinc-800/50 hover:text-white' }}"
                        >
                            @if ($link['icon'] === 'squares')
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                            @elseif ($link['icon'] === 'users')
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            @elseif ($link['icon'] === 'cube')
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                            @elseif ($link['icon'] === 'tag')
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                            @elseif ($link['icon'] === 'hashtag')
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5"/></svg>
                            @endif
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-zinc-800 p-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs text-zinc-600 transition hover:text-zinc-400">
                        ← Back to site
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs text-zinc-600 transition hover:text-red-400">
                            Log out
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex flex-1 flex-col">
                <header class="flex h-14 items-center justify-between border-b border-zinc-800 px-6">
                    <p class="text-sm text-zinc-500">
                        Logged in as <span class="text-zinc-300">{{ auth()->user()?->name }}</span>
                    </p>
                    <span class="rounded bg-zinc-800 px-2 py-0.5 text-xs text-zinc-500">
                        {{ config('app.name') }} Admin
                    </span>
                </header>

                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
