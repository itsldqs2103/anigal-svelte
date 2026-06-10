<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next): Response
    {
        $supported = array_column(config('i18nlocale.supportedLocales'), 'code');
        $locale = $request->cookie('locale');

        if (! $locale) {
            $locale = $request->getPreferredLanguage($supported);
        }

        if (! in_array($locale, $supported)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        $response = $next($request);

        if (! $request->hasCookie('locale')) {
            $response->withCookie(cookie('locale', $locale, 60 * 24 * 30));
        }

        return $response;
    }
}
