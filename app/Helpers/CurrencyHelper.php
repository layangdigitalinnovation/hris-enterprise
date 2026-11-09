<?php

namespace App\Helpers;

use App\Models\SystemSetting;

class CurrencyHelper
{
    public const CURRENCY_IDR = 'IDR';
    public const CURRENCY_SAR = 'SAR';

    /**
     * Supported currencies list.
     */
    public static function supportedCurrencies(): array
    {
        return [
            self::CURRENCY_IDR => [
                'code' => 'IDR',
                'name' => 'Rupiah Indonesia',
                'name_ar' => 'روبية إندونيسية',
                'symbol' => 'Rp',
                'symbol_ar' => 'Rp',
                'decimals' => 0,
                'country' => 'Indonesia',
                'flag' => '🇮🇩',
            ],
            self::CURRENCY_SAR => [
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'name_ar' => 'ريال سعودي',
                'symbol' => 'SAR',
                'symbol_ar' => 'ر.س',
                'decimals' => 2,
                'country' => 'Arab Saudi',
                'flag' => '🇸🇦',
            ],
        ];
    }

    /**
     * Get active default currency based on system configuration.
     */
    public static function defaultCurrency(): string
    {
        $base = strtoupper((string) SystemSetting::get('base_currency', self::CURRENCY_IDR));
        return in_array($base, [self::CURRENCY_IDR, self::CURRENCY_SAR], true) ? $base : self::CURRENCY_IDR;
    }

    /**
     * Format a monetary amount according to currency and current locale.
     */
    public static function format(?float $amount, ?string $currency = null, bool $withSymbol = true, ?int $decimals = null): string
    {
        $amount = (float) ($amount ?? 0);
        $curr = strtoupper(trim($currency ?: static::defaultCurrency()));
        if ($curr !== self::CURRENCY_SAR && $curr !== self::CURRENCY_IDR) {
            $curr = static::defaultCurrency();
        }

        $locale = app()->getLocale();
        $isArabic = ($locale === 'ar');

        if ($curr === self::CURRENCY_SAR) {
            $dec = $decimals !== null ? $decimals : 2;
            $formattedNumber = number_format($amount, $dec, '.', ',');

            if (!$withSymbol) {
                return $formattedNumber;
            }

            if ($isArabic) {
                return $formattedNumber . ' ر.س';
            }

            return 'SAR ' . $formattedNumber;
        }

        // Default IDR
        $dec = $decimals !== null ? $decimals : 0;
        $formattedNumber = number_format($amount, $dec, ',', '.');

        if (!$withSymbol) {
            return $formattedNumber;
        }

        return 'Rp ' . $formattedNumber;
    }

    /**
     * Get currency symbol.
     */
    public static function symbol(?string $currency = null): string
    {
        $curr = strtoupper(trim($currency ?: static::defaultCurrency()));
        $isArabic = (app()->getLocale() === 'ar');

        if ($curr === self::CURRENCY_SAR) {
            return $isArabic ? 'ر.س' : 'SAR';
        }

        return 'Rp';
    }

    /**
     * Get currency display label (with flag).
     */
    public static function label(?string $currency = null): string
    {
        $curr = strtoupper(trim($currency ?: static::defaultCurrency()));
        $isArabic = (app()->getLocale() === 'ar');

        if ($curr === self::CURRENCY_SAR) {
            return $isArabic ? '🇸🇦 ر.س (ريال سعودي)' : '🇸🇦 SAR (Saudi Riyal)';
        }

        return $isArabic ? '🇮🇩 Rp (روبية إندونيسية)' : '🇮🇩 IDR (Rupiah)';
    }

    /**
     * Get active exchange rate from SAR to IDR.
     */
    public static function getSarToIdrRate(): float
    {
        return (float) SystemSetting::get('exchange_rate_sar_to_idr', 4250.00);
    }

    /**
     * Convert amount between currencies.
     */
    public static function convert(float $amount, string $from, string $to, ?float $customRate = null): float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($from === $to) {
            return $amount;
        }

        $rate = $customRate ?: static::getSarToIdrRate();
        if ($rate <= 0) {
            $rate = 4250.00;
        }

        if ($from === self::CURRENCY_SAR && $to === self::CURRENCY_IDR) {
            return round($amount * $rate, 2);
        }

        if ($from === self::CURRENCY_IDR && $to === self::CURRENCY_SAR) {
            return round($amount / $rate, 2);
        }

        return $amount;
    }

    /**
     * Spelled-out words (Terbilang) for receipts and payslips.
     */
    public static function terbilang(float $amount, ?string $currency = self::CURRENCY_IDR): string
    {
        $curr = strtoupper(trim($currency ?: self::CURRENCY_IDR));
        $locale = app()->getLocale();

        $abs = abs((int) $amount);
        $words = static::numberToWordsIndonesian($abs);

        if ($locale === 'ar') {
            if ($curr === self::CURRENCY_SAR) {
                return $words . ' ريال سعودي';
            }
            return $words . ' روبية إندونيسية';
        }

        if ($curr === self::CURRENCY_SAR) {
            return ucwords(trim($words)) . ' Riyal Saudi';
        }

        return ucwords(trim($words)) . ' Rupiah';
    }

    private static function numberToWordsIndonesian(int $number): string
    {
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($number < 12) {
            return $words[$number];
        }
        if ($number < 20) {
            return static::numberToWordsIndonesian($number - 10) . ' Belas';
        }
        if ($number < 100) {
            return static::numberToWordsIndonesian((int) ($number / 10)) . ' Puluh ' . static::numberToWordsIndonesian($number % 10);
        }
        if ($number < 200) {
            return 'Seratus ' . static::numberToWordsIndonesian($number - 100);
        }
        if ($number < 1000) {
            return static::numberToWordsIndonesian((int) ($number / 100)) . ' Ratus ' . static::numberToWordsIndonesian($number % 100);
        }
        if ($number < 2000) {
            return 'Seribu ' . static::numberToWordsIndonesian($number - 1000);
        }
        if ($number < 1000000) {
            return static::numberToWordsIndonesian((int) ($number / 1000)) . ' Ribu ' . static::numberToWordsIndonesian($number % 1000);
        }
        if ($number < 1000000000) {
            return static::numberToWordsIndonesian((int) ($number / 1000000)) . ' Juta ' . static::numberToWordsIndonesian($number % 1000000);
        }
        if ($number < 1000000000000) {
            return static::numberToWordsIndonesian((int) ($number / 1000000000)) . ' Miliar ' . static::numberToWordsIndonesian($number % 1000000000);
        }
        return (string) $number;
    }
}

