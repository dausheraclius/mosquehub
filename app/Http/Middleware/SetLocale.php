<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Supported locales for the admin panel.
     */
    private const SUPPORTED = ['id', 'en'];

    /**
     * Default locale used when none is specified.
     */
    private const DEFAULT = 'id';

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale') ?? session('locale') ?? self::DEFAULT;

        if (! in_array($locale, self::SUPPORTED, true)) {
            abort(404);
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
}
