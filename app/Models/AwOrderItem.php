<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AwOrderItem extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function order(): BelongsTo {
        return $this->belongsTo(AwOrder::class, 'order_id');
    }

    public function product(): BelongsTo {
        return $this->belongsTo(AwProduct::class, 'product_id');
    }

    public function variant(): BelongsTo {
        return $this->belongsTo(AwProductVariant::class, 'variant_id');
    }

    public function unit(): BelongsTo {
        return $this->belongsTo(AwUnit::class, 'unit_id');
    }

    public function warehouse(): BelongsTo {
        return $this->belongsTo(AwWarehouse::class, 'warehouse_id');
    }
}