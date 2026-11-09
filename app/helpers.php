<?php

use App\Helpers\CurrencyHelper;

if (!function_exists('format_currency')) {
    /**
     * Format a monetary amount with currency symbol.
     */
    function format_currency($amount, ?string $currency = null, bool $withSymbol = true, ?int $decimals = null): string
    {
        return CurrencyHelper::format($amount, $currency, $withSymbol, $decimals);
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol.
     */
    function currency_symbol(?string $currency = null): string
    {
        return CurrencyHelper::symbol($currency);
    }
}

if (!function_exists('currency_label')) {
    /**
     * Get currency display label with flag.
     */
    function currency_label(?string $currency = null): string
    {
        return CurrencyHelper::label($currency);
    }
}

if (!function_exists('convert_currency')) {
    /**
     * Convert currency.
     */
    function convert_currency(float $amount, string $from, string $to, ?float $rate = null): float
    {
        return CurrencyHelper::convert($amount, $from, $to, $rate);
    }
}

