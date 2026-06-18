<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale)
    {
        if (!in_array($locale, ['de', 'en'])) {
            abort(400);
        }

        $request->session()->put('locale', $locale);

        if (Auth::check()) {
            Auth::user()->update(['locale' => $locale]);
        }

        $url = $request->headers->get('referer', '/dashboard');

        return redirect($url)->withCookie(
            Cookie::make('locale', $locale, 60 * 24 * 365)
        );
    }
}
