<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwProduct extends Model
{
   use SoftDeletes;

    protected $guarded = [];


    public function primaryCategory() {
        return $this->hasOne(AwProductCategory::class, 'product_id')->where('is_primary', 1);
    }

    public function variants()
    {
        return $this->hasMany(AwProductVariant::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(AwProductImage::class, 'product_id')->orderBy('position', 'asc');
    }

    public function primaryImage()
    {
        return $this->hasOne(AwProductImage::class, 'product_id')->where('position', 0);
    }

    public function secondaryImages()
    {
        return $this->hasMany(AwProductImage::class, 'product_id')->where('position', '!=', 0);
    }

    public function units()
    {
        return $this->hasMany(AwProductUnit::class, 'product_id');
    }

    public function prices()
    {
        return $this->hasMany(AwPrice::class, 'product_id');
    }

    public function bundle()
    {
        return $this->hasOne(AwBundle::class, 'product_id');
    }

    public function supplierWarehouseProducts()
    {
        return $this->hasMany(AwSupplierWarehouseProduct::class, 'product_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(AwInventoryMovement::class, 'product_id');
    }

    public function tags()
    {
        return $this->belongsToMany(AwTag::class, 'aw_product_tags', 'product_id', 'tag_id');
    }

    public function brand()
    {
        return $this->belongsTo(AwBrand::class, 'brand_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            AwCategory::class, 
            'aw_product_categories',
            'product_id',
            'category_id'
        )->withPivot('is_primary')
        ->withTimestamps();
    }

    public function substitutes()
    {
        return $this->belongsToMany(
            AwProduct::class, 
            'aw_product_substitutes',
            'product_id', 
            'substitute_id'
        )->withTimestamps();
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
