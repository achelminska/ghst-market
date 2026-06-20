<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['title', 'description', 'price', 'file_path', 'thumbnail', 'cover_image', 'category_id', 'user_id'])]
class Product extends Model
{
    use HasFactory, HasUuids;

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn ($product) => $product->slug = Str::slug($product->title));
    }
}
