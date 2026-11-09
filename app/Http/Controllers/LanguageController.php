<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    /**
     * Switch application language between Indonesian (id) and Arabic (ar).
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $allowedLocales = ['id', 'ar'];

        if (!in_array($locale, $allowedLocales, true)) {
            $locale = 'id';
        }

        $request->session()->put('locale', $locale);
        Cookie::queue('locale', $locale, 60 * 24 * 365); // 1 year
        App::setLocale($locale);

        return redirect()->back();
    }
}

