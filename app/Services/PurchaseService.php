<?php

namespace App\Services;

use App\Exceptions\AlreadyPurchasedException;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function purchase(User $user, Product $product): Purchase
    {
        if ($user->purchases()->where('product_id', $product->id)->exists()) {
            throw new AlreadyPurchasedException;
        }

        if ($product->price > 0 && $user->balance < $product->price) {
            throw new InsufficientBalanceException;
        }

        return DB::transaction(function () use ($user, $product) {
            if ($product->price > 0) {
                $user->decrement('balance', $product->price);
                $product->user->increment('balance', $product->price);
            }

            return $user->purchases()->create([
                'product_id' => $product->id,
                'price_paid' => $product->price,
            ]);
        });
    }
}
