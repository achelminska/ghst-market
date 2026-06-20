<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.admin-login')] class extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public string $error = '';

    public function login(): void
    {
        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->error = 'Invalid credentials.';
            return;
        }

        if (! Auth::user()->is_admin) {
            Auth::logout();
            $this->error = 'Access denied. Admin only.';
            return;
        }

        session()->regenerate();
        $this->redirect(route('admin.dashboard'), navigate: true);
    }
};
?>

<div>
    <div class="flex min-h-screen items-center justify-center bg-zinc-950 px-6">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <p class="text-sm font-medium text-zinc-500 uppercase tracking-widest">{{ config('app.name') }}</p>
                <h1 class="mt-2 text-2xl font-bold text-white">Admin panel</h1>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-8">
                @if ($error)
                    <div class="mb-4 rounded-xl bg-red-500/10 px-4 py-3 text-sm text-red-400">
                        {{ $error }}
                    </div>
                @endif

                <form wire:submit="login" class="space-y-4">
                    <flux:input wire:model="email" type="email" label="Email" placeholder="admin@example.com" autofocus autocomplete="email" />
                    @error('email') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

                    <flux:input wire:model="password" type="password" label="Password" viewable />
                    @error('password') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

                    <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove>Sign in to Admin</span>
                        <span wire:loading>Signing in...</span>
                    </flux:button>
                </form>
            </div>

            <a href="{{ route('home') }}" class="mt-6 block text-center text-sm text-zinc-600 hover:text-zinc-400">
                ← Back to site
            </a>
        </div>
    </div>
</div>
