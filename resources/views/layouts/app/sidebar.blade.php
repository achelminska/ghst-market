<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">

        <flux:header class="border-b border-zinc-800 bg-zinc-950/95 backdrop-blur-sm" sticky>
            <a href="{{ route('home') }}" wire:navigate class="me-6 shrink-0">
                <img src="{{ asset('images/ghst-icon.png') }}" alt="GHST Market" class="h-14 w-auto mix-blend-screen">
            </a>

            <flux:navbar class="-mb-px flex">
                <flux:navbar.item :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                    Browse
                </flux:navbar.item>
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="hidden lg:flex">
                    Dashboard
                </flux:navbar.item>
                <flux:navbar.item :href="route('my-purchases')" :current="request()->routeIs('my-purchases')" wire:navigate class="hidden lg:flex">
                    My purchases
                </flux:navbar.item>
                <flux:navbar.item :href="route('my-products.index')" :current="request()->routeIs('my-products.*')" wire:navigate class="hidden lg:flex">
                    My products
                </flux:navbar.item>
                @if (auth()->user()?->is_admin)
                    <flux:navbar.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.*')" wire:navigate class="hidden lg:flex">
                        Admin
                    </flux:navbar.item>
                @endif
            </flux:navbar>

            <flux:spacer />

            @auth
                <a href="{{ route('wallet') }}" wire:navigate class="me-4 hidden text-sm text-zinc-400 transition-colors hover:text-white lg:block">
                    ${{ number_format(auth()->user()?->balance ?? 0, 2) }}
                </a>

                <flux:dropdown position="bottom" align="end">
                    <flux:profile
                        :name="auth()->user()?->name"
                        :initials="auth()->user()?->initials()"
                        :avatar="auth()->user()?->avatarUrl()"
                        circle
                        icon-trailing="chevron-down"
                        class="cursor-pointer max-lg:[&>span]:hidden"
                    />

                    <flux:menu>
                        <div class="flex items-center gap-3 px-3 py-2">
                            <flux:avatar :name="auth()->user()?->name" :initials="auth()->user()?->initials()" :src="auth()->user()?->avatarUrl()" circle />
                            <div>
                                <p class="text-sm font-medium text-white">{{ auth()->user()?->name }}</p>
                                <p class="text-xs text-zinc-500">{{ auth()->user()?->email }}</p>
                            </div>
                        </div>

                        <flux:menu.separator />

                    <flux:menu.item :href="route('wallet')" icon="banknotes" wire:navigate>
                        Wallet — ${{ number_format(auth()->user()?->balance ?? 0, 2) }}
                    </flux:menu.item>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            Settings
                        </flux:menu.item>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="logout-button">
                                Log out
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" wire:navigate class="text-sm text-zinc-400 transition-colors hover:text-white">
                        Log in
                    </a>
                    <a href="{{ route('register') }}" wire:navigate class="rounded-lg bg-white px-4 py-1.5 text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-100">
                        Sign up
                    </a>
                </div>
            @endauth
        </flux:header>

        <flux:sidebar collapsible="mobile" sticky class="border-e border-zinc-800 bg-zinc-950 lg:hidden">
            <flux:sidebar.header>
                <a href="{{ route('home') }}" wire:navigate class="text-base font-semibold tracking-tight text-white">
                    {{ config('app.name') }}
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="magnifying-glass" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                    Browse
                </flux:sidebar.item>
                @auth
                    <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        Dashboard
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('my-purchases')" :current="request()->routeIs('my-purchases')" wire:navigate>
                        My purchases
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('my-products.index')" :current="request()->routeIs('my-products.*')" wire:navigate>
                        My products
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('wallet')" :current="request()->routeIs('wallet')" wire:navigate>
                        Wallet — ${{ number_format(auth()->user()?->balance ?? 0, 2) }}
                    </flux:sidebar.item>
                    @if (auth()->user()?->is_admin)
                        <flux:sidebar.item icon="shield-check" :href="route('admin.dashboard')" :current="request()->routeIs('admin.*')" wire:navigate>
                            Admin
                        </flux:sidebar.item>
                    @endif
                @else
                    <flux:sidebar.item icon="arrow-right-end-on-rectangle" :href="route('login')" wire:navigate>
                        Log in
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-plus" :href="route('register')" wire:navigate>
                        Sign up
                    </flux:sidebar.item>
                @endauth
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
