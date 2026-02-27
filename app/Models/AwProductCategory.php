<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwProductCategory extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(AwProduct::class);
    }

    public function category()
    {
        return $this->belongsTo(AwCategory::class, 'category_id');
    }
}
