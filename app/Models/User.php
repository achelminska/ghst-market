<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'balance', 'is_admin', 'is_active', 'username', 'avatar', 'profile_background', 'profile_banner'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }

    public function profileBannerUrl(): ?string
    {
        return $this->profile_banner ? Storage::url($this->profile_banner) : null;
    }

    /** @return array<string, string> */
    public static function profileBackgroundPresets(): array
    {
        return [
            'default' => 'Default',
            'violet' => 'Violet',
            'blue' => 'Blue',
            'emerald' => 'Emerald',
            'rose' => 'Rose',
            'amber' => 'Amber',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (! $user->username) {
                $base = Str::slug($user->name);
                $username = $base;
                $i = 2;
                while (self::withTrashed()->where('username', $username)->exists()) {
                    $username = $base.'-'.$i++;
                }
                $user->username = $username;
            }
        });

        /**
         * Free up unique fields (email, username) on soft delete
         * so the same email can be used to register a new account.
         */
        static::softDeleted(function (self $user) {
            $suffix = '.deleted.'.now()->timestamp;

            $user->forceFill([
                'email' => $user->email.$suffix,
                'username' => $user->username.$suffix,
            ])->saveQuietly();
        });
    }
}
