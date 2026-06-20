<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-800 bg-zinc-900">
            <flux:sidebar.header class="border-b border-zinc-800 py-4">
                <a href="{{ route('home') }}" wire:navigate class="text-base font-semibold tracking-tight text-white">
                    {{ config('app.name') }}
                </a>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="py-4">
                {{-- Overview --}}
                <flux:sidebar.group :heading="__('Overview')" class="grid">
                    <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('products.index')" :current="request()->routeIs('products.*')" wire:navigate>
                        {{ __('Browse') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                {{-- My account --}}
                <flux:sidebar.group :heading="__('My account')" class="grid">
                    <flux:sidebar.item icon="archive-box" :href="route('my-purchases')" :current="request()->routeIs('my-purchases')" wire:navigate>
                        {{ __('My purchases') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('my-products.index')" :current="request()->routeIs('my-products.*')" wire:navigate>
                        {{ __('My products') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('wallet')" :current="request()->routeIs('wallet')" wire:navigate>
                        {{ __('Wallet') }}
                        <span class="ml-auto text-xs text-zinc-500">${{ number_format(auth()->user()?->balance ?? 0, 2) }}</span>
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if (auth()->user()?->is_admin)
                    {{-- Admin --}}
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
                        <flux:sidebar.item icon="tag" :href="route('admin.categories')" :current="request()->routeIs('admin.categories*')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="hashtag" :href="route('admin.tags')" :current="request()->routeIs('admin.tags*')" wire:navigate>
                            {{ __('Tags') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block border-t border-zinc-800" :name="auth()->user()?->name" />
        </flux:sidebar>

        {{-- Mobile header --}}
        <flux:header class="border-b border-zinc-800 bg-zinc-950 lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('home') }}" wire:navigate class="text-sm font-semibold text-white">
                {{ config('app.name') }}
            </a>

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="flex items-center gap-2 px-2 py-2 text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                                <div>
                                    <p class="font-medium text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-zinc-500">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer" data-test="logout-button">
                                {{ __('Log out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
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
