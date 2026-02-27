<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwPromotion extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'category_id' => 'array',
        'product_id' => 'array',
        'variant_id' => 'array',
        'unit_id' => 'array',
        'warehouse_id' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'auto_applicable' => 'boolean',
        'status' => 'boolean',
        'discount_type' => 'boolean',
    ];

    /**
     * Get the type label attribute
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'catdisc' => 'Discount on Category',
            'prodisc' => 'Discount on Item',
            'cardisc' => 'Discount on Cart Amount',
            'buyxgetx' => 'Buy X Get Y',
            default => 'Unknown'
        };
    }

    /**
     * Get the discount type label attribute
     */
    public function getDiscountTypeLabelAttribute(): string
    {
        return $this->discount_type ? 'Fixed' : 'Percentage';
    }

    /**
     * Get the status badge attribute
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    /**
     * Poster URL accessor
     */
    public function getPosterUrlAttribute(): ?string
    {
        if ($this->posters) {
            $path = storage_path('app/public/promotions/' . $this->posters);
            if (file_exists($path) && is_file($path)) {
                return asset('storage/promotions/' . $this->posters);
            }
        }
        return null;
    }

    /**
     * Related categories (for catdisc type)
     */
    public function categories()
    {
        if (!$this->category_id)
            return collect();
        return AwCategory::whereIn('id', $this->category_id)->get();
    }

    /**
     * Related products (for prodisc type)
     */
    public function products()
    {
        if (!$this->product_id)
            return collect();
        return AwProduct::whereIn('id', $this->product_id)->get();
    }

    /**
     * X Product (for buyxgetx type)
     */
    public function xProduct(): BelongsTo
    {
        return $this->belongsTo(AwProduct::class, 'x_product');
    }

    /**
     * X Variant
     */
    public function xVariant(): BelongsTo
    {
        return $this->belongsTo(AwProductVariant::class, 'x_variant');
    }

    /**
     * Y Product (for buyxgetx type)
     */
    public function yProduct(): BelongsTo
    {
        return $this->belongsTo(AwProduct::class, 'y_item');
    }

    /**
     * Y Variant
     */
    public function yVariant(): BelongsTo
    {
        return $this->belongsTo(AwProductVariant::class, 'y_variant');
    }

    /**
     * Scope for active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for currently valid promotions (within date range)
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $now);
        });
    }
}
