<?php

/**
 * Currency Helper Functions
 * 
 * Global helper functions for currency formatting throughout the application.
 * These functions use the CurrencyService singleton for consistency.
 */

use App\Services\CurrencyService;
use App\Models\AwCurrency;

if (!function_exists('currency_format')) {
    /**
     * Convert and format a price from base currency to selected currency
     * 
     * @param float|int|null $amount Amount in base currency
     * @param AwCurrency|null $currency Optional specific currency
     * @return string Formatted price string
     */
    function currency_format($amount, ?AwCurrency $currency = null): string
    {
        if ($amount === null) {
            $amount = 0;
        }

        $service = app(CurrencyService::class);
        return $service->convertAndFormat((float) $amount, $currency);
    }
}

if (!function_exists('currency_convert')) {
    /**
     * Convert a price from base currency to selected currency (without formatting)
     * 
     * @param float|int|null $amount Amount in base currency
     * @param AwCurrency|null $currency Optional specific currency
     * @return float Converted amount
     */
    function currency_convert($amount, ?AwCurrency $currency = null): float
    {
        if ($amount === null) {
            return 0.0;
        }

        $service = app(CurrencyService::class);
        return $service->convert((float) $amount, $currency);
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get the symbol of the current selected currency
     * 
     * @return string Currency symbol
     */
    function currency_symbol(): string
    {
        $service = app(CurrencyService::class);
        $currency = $service->getSelectedCurrency();
        return $currency->symbol ?? '$';
    }
}

if (!function_exists('currency_code')) {
    /**
     * Get the ISO code of the current selected currency
     * 
     * @return string Currency ISO code
     */
    function currency_code(): string
    {
        $service = app(CurrencyService::class);
        $currency = $service->getSelectedCurrency();
        return $currency->iso_code ?? 'USD';
    }
}

if (!function_exists('base_currency_format')) {
    /**
     * Format a price in base currency (no conversion)
     * 
     * @param float|int|null $amount Amount in base currency
     * @return string Formatted price string
     */
    function base_currency_format($amount): string
    {
        if ($amount === null) {
            $amount = 0;
        }

        $service = app(CurrencyService::class);
        $baseCurrency = $service->getBaseCurrency();

        if ($baseCurrency) {
            return $baseCurrency->formatPrice((float) $amount);
        }

        return '$' . number_format((float) $amount, 2);
    }
}
