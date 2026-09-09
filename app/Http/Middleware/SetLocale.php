<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

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

        // Jadikan locale sebagai default pembuatan URL route. Dengan ini semua
        // route('...') (tanpa argumen locale) otomatis menghasilkan URL berprefix
        // {locale} (mis. /id/pengumuman), termasuk redirect setelah store/update.
        URL::defaults(['locale' => $locale]);

        // Parameter {locale} hanya untuk routing/setting locale. Jangan diteruskan
        // ke controller sebagai argumen — karena Laravel melempar parameter route
        // secara posisional, nilai {locale} ini menggeser parameter berikutnya
        // ({id}/{model}) sehingga update/delete menerima "id"/"en" sebagai ID.
        $request->route()?->forgetParameter('locale');

        return $next($request);
    }
}
