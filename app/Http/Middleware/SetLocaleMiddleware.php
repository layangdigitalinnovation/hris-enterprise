<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $default = App::getLocale() ?: config('app.locale', 'id');
        $locale = $request->session()->get('locale', $request->cookie('locale', $default));

        if (!in_array($locale, ['id', 'ar'], true)) {
            $locale = 'id';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        // Share locale variables globally across all views
        view()->share('currentLocale', $locale);
        view()->share('isRtl', $locale === 'ar');

        return $next($request);
    }
}

