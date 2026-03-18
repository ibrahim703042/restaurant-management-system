<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function set(Request $request, string $locale): RedirectResponse
    {
        $locales = config('app.available_locales', ['en', 'fr']);
        abort_unless(in_array($locale, $locales, true), 404);
        $request->session()->put('locale', $locale);

        return back();
    }
}
