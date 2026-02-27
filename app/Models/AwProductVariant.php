<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwProductVariant extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(AwProduct::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(AwProductImage::class, 'variant_id')->orderBy('position', 'asc');
    }

    public function attributes()
    {
        return $this->belongsToMany(
            AwAttributeValue::class,
            'aw_variant_attribute_values',
            'variant_id',
            'attribute_value_id'
        );
    }

    public function supplierWarehouseProducts()
    {
        return $this->hasMany(AwSupplierWarehouseProduct::class, 'variant_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(AwInventoryMovement::class, 'variant_id');
    }

    public function units()
    {
        return $this->hasMany(AwProductUnit::class, 'variant_id');
    }

    public function prices()
    {
        return $this->hasMany(AwPrice::class, 'variant_id')->where('product_id', $this->product_id)->where('variant_id', $this->variant_id);;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
