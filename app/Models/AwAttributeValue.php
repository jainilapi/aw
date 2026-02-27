<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwAttributeValue extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function attribute()
    {
        return $this->belongsTo(AwAttribute::class);
    }

    public function variants()
    {
        return $this->belongsToMany(
            AwProductVariant::class,
            'aw_variant_attribute_values',
            'attribute_value_id',
            'variant_id'
        );
    }
}
