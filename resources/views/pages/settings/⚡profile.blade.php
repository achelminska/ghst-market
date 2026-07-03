<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $username = '';
    public string $profile_background = 'default';
    public $avatar = null;
    public $profileBanner = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username ?? '';
        $this->profile_background = $user->profile_background ?? 'default';
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            ...$this->profileRules($user->id),
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_-]+$/', Rule::unique(User::class)->ignore($user->id)],
            'profile_background' => 'required|in:'.implode(',', array_keys(User::profileBackgroundPresets())),
        ]);

        if ($this->avatar) {
            $this->validate(['avatar' => 'image|mimes:png,jpg,jpeg,webp|max:2048']);

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $this->avatar->store('avatars', 'public');
            $this->avatar = null;
        }

        if ($this->profileBanner) {
            $this->validate(['profileBanner' => 'image|mimes:png,jpg,jpeg,webp|max:5120']);

            if ($user->profile_banner) {
                Storage::disk('public')->delete($user->profile_banner);
            }

            $validated['profile_banner'] = $this->profileBanner->store('profile-banners', 'public');
            $this->profileBanner = null;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }
        Flux::toast(variant: 'success', text: 'Avatar removed.');
    }

    public function removeProfileBanner(): void
    {
        $user = Auth::user();
        if ($user->profile_banner) {
            Storage::disk('public')->delete($user->profile_banner);
            $user->update(['profile_banner' => null]);
        }
        Flux::toast(variant: 'success', text: 'Banner removed.');
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Flux::toast(text: __('A new verification link has been sent to your email address.'));
    }

    #[Computed]
    public function backgroundPresets(): array
    {
        return User::profileBackgroundPresets();
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name, email, avatar and public profile look')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            {{-- Avatar --}}
            <div>
                <p class="mb-3 block text-sm font-medium text-zinc-300">Avatar</p>
                <div class="flex items-center gap-4">
                    <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-full bg-zinc-700">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" alt="" class="block h-16 w-16 object-cover">
                        @elseif (auth()->user()->avatarUrl())
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="block h-16 w-16 object-cover">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center text-lg font-semibold text-white">
                                {{ auth()->user()->initials() }}
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="cursor-pointer rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-300 transition hover:border-zinc-600 hover:text-white">
                            {{ $avatar ? 'Change' : 'Upload photo' }}
                            <input type="file" wire:model="avatar" accept="image/*" class="sr-only">
                        </label>
                        @if (auth()->user()->avatar && !$avatar)
                            <flux:button wire:click="removeAvatar" variant="ghost" size="sm" class="text-zinc-600 hover:text-red-400">
                                Remove
                            </flux:button>
                        @endif
                    </div>
                </div>
                @error('avatar') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Page background --}}
            <div>
                <p class="mb-3 block text-sm font-medium text-zinc-300">Page background</p>
                <p class="mb-3 text-xs text-zinc-600">Upload a tileable image — it repeats behind your entire public profile, like on itch.io.</p>
                @if ($profileBanner || auth()->user()->profileBannerUrl())
                    <div
                        class="h-32 overflow-hidden rounded-xl border border-zinc-800"
                        style="background-image: url('{{ $profileBanner ? $profileBanner->temporaryUrl() : auth()->user()->profileBannerUrl() }}'); background-repeat: repeat; background-size: auto;"
                    ></div>
                @else
                    <div @class([
                        'h-32 overflow-hidden rounded-xl border border-zinc-800 bg-gradient-to-br',
                        'from-zinc-900 to-zinc-950' => $profile_background === 'default',
                        'from-violet-950 via-zinc-900 to-zinc-950' => $profile_background === 'violet',
                        'from-blue-950 via-zinc-900 to-zinc-950' => $profile_background === 'blue',
                        'from-emerald-950 via-zinc-900 to-zinc-950' => $profile_background === 'emerald',
                        'from-rose-950 via-zinc-900 to-zinc-950' => $profile_background === 'rose',
                        'from-amber-950 via-zinc-900 to-zinc-950' => $profile_background === 'amber',
                    ])></div>
                @endif
                <div class="mt-3 flex items-center gap-2">
                    <label class="cursor-pointer rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-1.5 text-sm text-zinc-300 transition hover:border-zinc-600 hover:text-white">
                        Upload background
                        <input type="file" wire:model="profileBanner" accept="image/*" class="sr-only">
                    </label>
                    @if (auth()->user()->profile_banner && !$profileBanner)
                        <flux:button wire:click="removeProfileBanner" variant="ghost" size="sm" class="text-zinc-600 hover:text-red-400">
                            Remove
                        </flux:button>
                    @endif
                </div>
                @error('profileBanner') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Background color --}}
            <div>
                <p class="mb-3 block text-sm font-medium text-zinc-300">Fallback color</p>
                <p class="mb-3 text-xs text-zinc-600">Used when no background image is uploaded.</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->backgroundPresets as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="profile_background" value="{{ $key }}" class="sr-only peer">
                            <span @class([
                                'flex h-10 w-10 items-center justify-center rounded-full border-2 transition',
                                'border-white ring-2 ring-white ring-offset-2 ring-offset-zinc-900' => $profile_background === $key,
                                'border-transparent' => $profile_background !== $key,
                                'bg-gradient-to-br from-zinc-700 to-zinc-900' => $key === 'default',
                                'bg-gradient-to-br from-violet-700 to-violet-950' => $key === 'violet',
                                'bg-gradient-to-br from-blue-700 to-blue-950' => $key === 'blue',
                                'bg-gradient-to-br from-emerald-700 to-emerald-950' => $key === 'emerald',
                                'bg-gradient-to-br from-rose-700 to-rose-950' => $key === 'rose',
                                'bg-gradient-to-br from-amber-700 to-amber-950' => $key === 'amber',
                            ]) title="{{ $label }}"></span>
                        </label>
                    @endforeach
                </div>
                @error('profile_background') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input.group
                    :label="__('Username')"
                    :description="__('Used in your public profile URL. Letters, numbers, hyphens and underscores only.')"
                    name="username"
                >
                    <flux:input.group.prefix>@</flux:input.group.prefix>
                    <flux:input wire:model="username" type="text" required autocomplete="username" />
                </flux:input.group>
                @error('username') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="cursor-pointer text-sm" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
