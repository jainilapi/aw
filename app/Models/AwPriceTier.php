<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwPriceTier extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function price()
    {
        return $this->belongsTo(AwPrice::class, 'price_id');
    }
}
