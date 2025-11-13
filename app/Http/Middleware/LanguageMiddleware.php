<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Supported languages
     */
    protected $supportedLanguages = ['en', 'uz', 'ru'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for language parameter in URL
        if ($request->has('lang')) {
            $language = $request->get('lang');

            if (in_array($language, $this->supportedLanguages)) {
                Session::put('locale', $language);
                App::setLocale($language);

                // Remove lang parameter from URL and redirect
                $query = $request->query();
                unset($query['lang']);

                $url = $request->url();
                if (!empty($query)) {
                    $url .= '?' . http_build_query($query);
                }

                return redirect($url);
            }
        }

        // Check for language in session
        $locale = Session::get('locale', 'en');

        if (in_array($locale, $this->supportedLanguages)) {
            App::setLocale($locale);
        } else {
            // Default to English if invalid locale in session
            App::setLocale('en');
            Session::put('locale', 'en');
        }

        return $next($request);
    }
}
