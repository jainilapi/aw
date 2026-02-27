<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Currency Model
 * 
 * Represents a currency in the multi-currency system.
 * All prices are stored in base currency and converted using exchange_rate.
 */
class AwCurrency extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
        'sort_order' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Orders placed in this currency
     */
    public function orders(): HasMany
    {
        return $this->hasMany(AwOrder::class, 'currency_id');
    }

    /**
     * Carts using this currency
     */
    public function carts(): HasMany
    {
        return $this->hasMany(AwCart::class, 'currency_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope for active currencies only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get the base currency
     */
    public function scopeBase(Builder $query): Builder
    {
        return $query->where('is_base', true);
    }

    /**
     * Scope for frontend display order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get formatted symbol with proper position indicator
     */
    public function getFormattedSymbolAttribute(): string
    {
        return $this->symbol;
    }

    /**
     * Get display name with symbol
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->symbol})";
    }

    // ==================== HELPER METHODS ====================

    /**
     * Format a price in this currency
     * 
     * @param float $amount Amount in this currency
     * @return string Formatted price string
     */
    public function formatPrice(float $amount): string
    {
        $formattedAmount = number_format($amount, $this->decimal_places);

        if ($this->symbol_position === 'before') {
            return $this->symbol . $formattedAmount;
        }

        return $formattedAmount . $this->symbol;
    }

    /**
     * Convert an amount from base currency to this currency
     * 
     * @param float $baseAmount Amount in base currency
     * @return float Converted amount
     */
    public function convertFromBase(float $baseAmount): float
    {
        if ($this->is_base || $this->exchange_rate == 0) {
            return $baseAmount;
        }

        return round($baseAmount * $this->exchange_rate, $this->decimal_places);
    }

    /**
     * Convert an amount from this currency to base currency
     * 
     * @param float $amount Amount in this currency
     * @return float Amount in base currency
     */
    public function convertToBase(float $amount): float
    {
        if ($this->is_base || $this->exchange_rate == 0) {
            return $amount;
        }

        return round($amount / $this->exchange_rate, 2); // Base currency always 2 decimals
    }

    /**
     * Check if this is the base currency
     */
    public function isBaseCurrency(): bool
    {
        return (bool) $this->is_base;
    }

    /**
     * Set this currency as the base currency
     * Automatically unsets any previous base currency
     */
    public function setAsBase(): void
    {
        // Unset any existing base currency
        static::where('is_base', true)
            ->where('id', '!=', $this->id)
            ->update(['is_base' => false]);

        // Set this as base
        $this->update([
            'is_base' => true,
            'exchange_rate' => 1.000000, // Base currency always has rate of 1
        ]);
    }

    /**
     * Get the base currency
     */
    public static function getBaseCurrency(): ?self
    {
        return static::where('is_base', true)->first();
    }

    /**
     * Get all active currencies for frontend selector
     */
    public static function getActiveCurrencies()
    {
        return static::active()->ordered()->get();
    }
}
