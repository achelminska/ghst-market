<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable(['name'])]
class Tag extends Model
{
    use HasUuids;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    protected static function booted(): void
    {
        static::creating(fn ($tag) => $tag->slug = Str::slug($tag->name));
    }
}
