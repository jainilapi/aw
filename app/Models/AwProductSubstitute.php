<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwProductSubstitute extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(AwProduct::class, 'product_id');
    }

    public function substitute()
    {
        return $this->belongsTo(AwProduct::class, 'substitute_id');
    }
}
