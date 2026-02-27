<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwSupplierWarehouseProduct extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(AwWarehouse::class, 'warehouse_id');
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
        return $this->belongsTo(AwUnit::class, 'unit_id');
    }
}
