<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwAttribute extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function values()
    {
        return $this->hasMany(AwAttributeValue::class, 'attribute_id');
    }
}
