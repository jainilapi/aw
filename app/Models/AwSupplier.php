<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwSupplier extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'contact_info' => 'array',
    ];

    public function stocks()
    {
        return $this->hasMany(AwSupplierWarehouseProduct::class);
    }
}
