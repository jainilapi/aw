<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwProductUnit extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    public $timestamps = false;

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
        return $this->belongsTo(AwUnit::class, 'unit_id');
    }

    public function parentUnit()
    {
        return $this->belongsTo(AwProductUnit::class, 'parent_unit_id');
    }

    public function price()
    {
        return $this->hasOne(AwPrice::class, 'unit_id')->where('product_id', $this->product_id);
    }
}
