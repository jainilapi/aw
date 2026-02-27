<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwPrice extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(AwProduct::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(AwProductVariant::class, 'variant_id');
    }

    public function unit()
    {
        return $this->belongsTo(AwUnit::class, 'original_unit_id');
    }

    public function belongs()
    {
        return $this->belongsTo(AwProductUnit::class, 'unit_id');
    }

    public function tiers()
    {
        return $this->hasMany(AwPriceTier::class, 'price_id');
    }
}
