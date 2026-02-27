<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AwOrderStatusHistory extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function order() {
        return $this->belongsTo(AwOrder::class, 'order_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'changed_by');
    }
}