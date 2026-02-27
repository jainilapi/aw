<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AwUnit extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function productUnits()
    {
        return $this->hasMany(AwProductUnit::class, 'unit_id');
    }
}
