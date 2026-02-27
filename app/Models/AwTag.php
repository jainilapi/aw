<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwTag extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(AwProduct::class, 'aw_product_tags', 'tag_id', 'product_id');
    }

    public function productTags()
    {
        return $this->hasMany(AwProductTag::class, 'tag_id');
    }
}
