<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwPromotionUsage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    /**
     * Promotion relationship
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(AwPromotion::class, 'promotion_id');
    }

    /**
     * Customer relationship
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(AwCustomer::class, 'customer_id');
    }

    /**
     * Order relationship
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(AwOrder::class, 'order_id');
    }
}
