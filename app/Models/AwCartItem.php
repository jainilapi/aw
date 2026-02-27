<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwCartItem extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function cart() {
        return $this->belongsTo(AwCart::class, 'cart_id');
    }

    public function product() {
        return $this->belongsTo(AwProduct::class, 'product_id');
    }

    public function variant() {
        return $this->belongsTo(AwProductVariant::class, 'variant_id');
    }

    public function unit() {
        return $this->belongsTo(AwUnit::class, 'unit_id');
    }
}