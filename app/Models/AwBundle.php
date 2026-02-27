<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwBundle extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(AwProduct::class, 'product_id');
    }

    public function items()
    {
        return $this->hasMany(AwBundleItem::class, 'bundle_id');
    }
}
