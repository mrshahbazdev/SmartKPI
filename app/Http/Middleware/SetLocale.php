<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'de';

        if (Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;
        } elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }

        if (in_array($locale, ['de', 'en'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
