<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">

        <flux:header class="border-b border-zinc-800 bg-zinc-950/95 backdrop-blur-sm" sticky>
            <a href="{{ route('home') }}" wire:navigate class="me-6 shrink-0 text-base font-semibold tracking-tight text-white">
                {{ config('app.name') }}
            </a>

            <flux:navbar class="-mb-px hidden lg:flex">
                <flux:navbar.item :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                    Browse
                </flux:navbar.item>
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    Dashboard
                </flux:navbar.item>
                <flux:navbar.item :href="route('my-purchases')" :current="request()->routeIs('my-purchases')" wire:navigate>
                    My purchases
                </flux:navbar.item>
                <flux:navbar.item :href="route('my-products.index')" :current="request()->routeIs('my-products.*')" wire:navigate>
                    My products
                </flux:navbar.item>
                @if (auth()->user()?->is_admin)
                    <flux:navbar.item :href="route('admin.categories')" :current="request()->routeIs('admin.*')" wire:navigate>
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
                        icon-trailing="chevron-down"
                        class="cursor-pointer"
                    />

                    <flux:menu>
                        <div class="flex items-center gap-3 px-3 py-2">
                            <flux:avatar :name="auth()->user()?->name" :initials="auth()->user()?->initials()" />
                            <div>
                                <p class="text-sm font-medium text-white">{{ auth()->user()?->name }}</p>
                                <p class="text-xs text-zinc-500">{{ auth()->user()?->email }}</p>
                            </div>
                        </div>

                        <flux:menu.separator />

                        <flux:menu.item :href="route('wallet')" icon="banknotes" wire:navigate>
                            Wallet — ${{ number_format(auth()->user()?->balance ?? 0, 2) }}
                        </flux:menu.item>
                        <flux:menu.item :href="route('my-products.index')" icon="cube" wire:navigate>
                            My products
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

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
