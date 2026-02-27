<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $guarded = [];

    /**
     * Get the base currency for the application
     */
    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(AwCurrency::class, 'base_currency_id');
    }
}
