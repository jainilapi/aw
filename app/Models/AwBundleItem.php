<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwBundleItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function bundle()
    {
        return $this->belongsTo(AwBundle::class, 'bundle_id');
    }

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
        return $this->belongsTo(AwProductUnit::class, 'unit_id');
    }
}
