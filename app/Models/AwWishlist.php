<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwWishlist extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    public function product() {
        return $this->belongsTo(AwProduct::class, 'product_id');
    }

    public function variant() {
        return $this->belongsTo(AwProductVariant::class, 'variant_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function mergeGuestWishlistToUser($guestId, $userId) {
        $guestItems = self::where('guest_id', $guestId)->get();

        foreach ($guestItems as $item) {
            $exists = self::where('user_id', $userId)
                ->where('product_id', $item->product_id)
                ->where('variant_id', $item->variant_id)
                ->exists();

            if (!$exists) {
                $item->update([
                    'user_id' => $userId,
                    'guest_id' => null
                ]);
            } else {
                $item->forceDelete();
            }
        }
    }
}