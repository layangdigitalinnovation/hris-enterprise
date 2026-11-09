<?php

namespace App\Helpers;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;

class ArabicPdfHelper
{
    /**
     * Prepare HTML containing Arabic for DomPDF by shaping Arabic glyphs and applying BiDi.
     */
    public static function prepareHtml(string $html): string
    {
        if (app()->getLocale() !== 'ar') {
            return $html;
        }

        $arabic = new Arabic();

        // 1. Prevent Courier/Helvetica/Arial font override which causes ??? for Arabic glyphs
        $html = str_replace([
            'font-family: monospace',
            'font-family: \'Courier New\', monospace',
            'font-family: "Courier New", monospace',
            'font-family: \'Courier New\'',
            'font-family: "Courier New"',
            '\'Helvetica\'',
            '"Helvetica"',
            'Helvetica',
            '\'Arial\'',
            '"Arial"',
            'Arial',
        ], '\'DejaVu Sans\'', $html);

        // 2. Normalize numeric font-weights (e.g. font-weight: 800) to bold/normal.
        // DomPDF cannot match numeric font-weights for TrueType fonts and silently falls back to Helvetica (causing ???)
        $html = preg_replace('/font-weight:\s*(?:[6-9]00|bolder)\b/i', 'font-weight: bold', $html);
        $html = preg_replace('/font-weight:\s*(?:[1-5]00|lighter)\b/i', 'font-weight: normal', $html);

        // 2. Process text nodes between > and <
        $html = preg_replace_callback('/>([^<]+)</u', function ($matches) use ($arabic) {
            $text = $matches[1];
            // If segment contains any Arabic character
            if (preg_match('/\p{Arabic}/u', $text)) {
                $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $shaped = $arabic->utf8Glyphs($decoded, 1000, false, false);
                return '>' . $shaped . '<';
            }
            return $matches[0];
        }, $html);

        // 3. Process attributes like title, placeholder, alt
        $html = preg_replace_callback('/(title|placeholder|alt)="([^"]*)"/u', function ($matches) use ($arabic) {
            $attr = $matches[1];
            $text = $matches[2];
            if (preg_match('/\p{Arabic}/u', $text)) {
                $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $shaped = $arabic->utf8Glyphs($decoded, 1000, false, false);
                return $attr . '="' . $shaped . '"';
            }
            return $matches[0];
        }, $html);

        return $html;
    }

    /**
     * Render a view to a DomPDF instance, with automatic Arabic glyph shaping when in Arabic locale.
     */
    public static function loadView(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait')
    {
        $html = view($view, $data)->render();
        if (app()->getLocale() === 'ar') {
            $html = self::prepareHtml($html);
        }
        return Pdf::loadHTML($html)->setPaper($paper, $orientation);
    }
}
