<?php

namespace App\Services;

use App\Models\AwCurrency;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

/**
 * CurrencyService
 * 
 * Centralized service for all currency operations.
 * Handles conversion, formatting, and currency selection.
 * 
 * IMPORTANT: All prices in the database are stored in base currency.
 * This service converts them for display in customer's selected currency.
 */
class CurrencyService
{
    /**
     * Cache key for base currency
     */
    protected const BASE_CURRENCY_CACHE_KEY = 'base_currency';

    /**
     * Session key for selected currency
     */
    protected const SELECTED_CURRENCY_SESSION_KEY = 'selected_currency_id';

    /**
     * Cookie name for currency preference
     */
    public const CURRENCY_COOKIE_NAME = 'currency_id';

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Get the base currency from settings
     * 
     * @return AwCurrency|null
     */
    public function getBaseCurrency(): ?AwCurrency
    {
        return Cache::remember(self::BASE_CURRENCY_CACHE_KEY, self::CACHE_TTL, function () {
            // First try to get from settings
            $setting = Setting::first();
            if ($setting && $setting->base_currency_id) {
                return AwCurrency::find($setting->base_currency_id);
            }

            // Fallback to currency marked as base
            return AwCurrency::where('is_base', true)->first();
        });
    }

    /**
     * Get all active currencies for frontend selector
     * 
     * @return Collection
     */
    public function getActiveCurrencies(): Collection
    {
        return Cache::remember('active_currencies', self::CACHE_TTL, function () {
            return AwCurrency::active()->ordered()->get();
        });
    }

    /**
     * Get user's selected currency
     * Falls back to base currency if none selected
     * 
     * @return AwCurrency
     */
    public function getSelectedCurrency(): AwCurrency
    {
        // Try session first
        $currencyId = Session::get(self::SELECTED_CURRENCY_SESSION_KEY);

        // Try cookie if session is empty
        if (!$currencyId) {
            $currencyId = request()->cookie(self::CURRENCY_COOKIE_NAME);
        }

        if ($currencyId) {
            $currency = AwCurrency::find($currencyId);
            if ($currency && $currency->is_active) {
                return $currency;
            }
        }

        // Fallback to base currency
        $baseCurrency = $this->getBaseCurrency();

        // Ultimate fallback: create a default USD if nothing exists
        if (!$baseCurrency) {
            return $this->getDefaultCurrency();
        }

        return $baseCurrency;
    }

    /**
     * Set user's currency preference
     * 
     * @param int $currencyId
     * @return void
     */
    public function setSelectedCurrency(int $currencyId): void
    {
        $currency = AwCurrency::find($currencyId);

        if ($currency && $currency->is_active) {
            Session::put(self::SELECTED_CURRENCY_SESSION_KEY, $currencyId);
        }
    }

    /**
     * Convert amount from base currency to target currency
     * 
     * @param float $amount Amount in base currency
     * @param AwCurrency|null $toCurrency Target currency (defaults to selected)
     * @return float Converted amount
     */
    public function convert(float $amount, ?AwCurrency $toCurrency = null): float
    {
        $currency = $toCurrency ?? $this->getSelectedCurrency();

        if (!$currency || $currency->is_base) {
            return $amount;
        }

        return $currency->convertFromBase($amount);
    }

    /**
     * Format price with symbol and locale
     * 
     * @param float $amount Amount (already in target currency)
     * @param AwCurrency|null $currency Currency to format in (defaults to selected)
     * @return string Formatted price string
     */
    public function format(float $amount, ?AwCurrency $currency = null): string
    {
        $currency = $currency ?? $this->getSelectedCurrency();

        if (!$currency) {
            // Fallback formatting if no currency
            return '$' . number_format($amount, 2);
        }

        return $currency->formatPrice($amount);
    }

    /**
     * Convert and format in one step
     * Use this for displaying prices from database (which are in base currency)
     * 
     * @param float $baseAmount Amount in base currency
     * @param AwCurrency|null $currency Target currency (defaults to selected)
     * @return string Formatted price in target currency
     */
    public function convertAndFormat(float $baseAmount, ?AwCurrency $currency = null): string
    {
        $convertedAmount = $this->convert($baseAmount, $currency);
        return $this->format($convertedAmount, $currency);
    }

    /**
     * Get exchange rate for a currency relative to base
     * 
     * @param AwCurrency|null $currency
     * @return float
     */
    public function getExchangeRate(?AwCurrency $currency = null): float
    {
        $currency = $currency ?? $this->getSelectedCurrency();

        if (!$currency) {
            return 1.0;
        }

        return (float) $currency->exchange_rate;
    }

    /**
     * Get currency by ID
     * 
     * @param int $id
     * @return AwCurrency|null
     */
    public function getCurrencyById(int $id): ?AwCurrency
    {
        return AwCurrency::find($id);
    }

    /**
     * Get currency by ISO code
     * 
     * @param string $isoCode
     * @return AwCurrency|null
     */
    public function getCurrencyByCode(string $isoCode): ?AwCurrency
    {
        return AwCurrency::where('iso_code', strtoupper($isoCode))->first();
    }

    /**
     * Clear currency caches
     * Call this after updating currencies or settings
     */
    public function clearCache(): void
    {
        Cache::forget(self::BASE_CURRENCY_CACHE_KEY);
        Cache::forget('active_currencies');
    }

    /**
     * Get default currency object (for fallback when no currencies exist)
     * 
     * @return AwCurrency
     */
    protected function getDefaultCurrency(): AwCurrency
    {
        // Return a non-persisted default currency object
        $currency = new AwCurrency();
        $currency->id = 0;
        $currency->name = 'US Dollar';
        $currency->iso_code = 'USD';
        $currency->symbol = '$';
        $currency->exchange_rate = 1.0;
        $currency->is_base = true;
        $currency->is_active = true;
        $currency->decimal_places = 2;
        $currency->symbol_position = 'before';

        return $currency;
    }

    /**
     * Get currency data for JavaScript
     * 
     * @return array
     */
    public function getJsConfig(): array
    {
        $selected = $this->getSelectedCurrency();

        return [
            'selected_currency_id' => $selected->id ?? null,
            'selected_currency_code' => $selected->iso_code ?? 'USD',
            'selected_currency_symbol' => $selected->symbol ?? '$',
            'exchange_rate' => $this->getExchangeRate($selected),
            'decimal_places' => $selected->decimal_places ?? 2,
            'symbol_position' => $selected->symbol_position ?? 'before',
        ];
    }
}
