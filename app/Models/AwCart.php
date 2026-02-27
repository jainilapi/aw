<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwCart extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(AwCartItem::class, 'cart_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appliedCoupon()
    {
        return $this->belongsTo(AwPromotion::class, 'applied_coupon_id');
    }

    /**
     * Get the currency selected for this cart
     */
    public function currency()
    {
        return $this->belongsTo(AwCurrency::class, 'currency_id');
    }

    public static function mergeGuestCartToUser($guestId, $userId)
    {
        $guestCart = self::where('guest_id', $guestId)->first();
        if (!$guestCart)
            return;

        $userCart = self::firstOrCreate(['user_id' => $userId]);

        foreach ($guestCart->items as $item) {
            $existingItem = $userCart->items()
                ->where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->where('unit_id', $item->unit_id)
                ->first();

            if ($existingItem) {
                $existingItem->update(['quantity' => $existingItem->quantity + $item->quantity]);
                $item->delete();
            } else {
                $item->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
    }
}