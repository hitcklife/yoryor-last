<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get supported locales
        $supportedLocales = config('app.supported_locales', ['en', 'es', 'fr', 'de', 'ru', 'uz']);
        
        // Get locale from various sources
        $locale = $this->getLocaleFromRequest($request, $supportedLocales);
        
        // Set the application locale
        App::setLocale($locale);
        
        // Store in session for future requests
        Session::put('locale', $locale);
        
        return $next($request);
    }
    
    /**
     * Get locale from request
     */
    private function getLocaleFromRequest(Request $request, array $supportedLocales): string
    {
        // 1. Check URL parameter
        if ($request->has('lang') && in_array($request->get('lang'), $supportedLocales)) {
            return $request->get('lang');
        }
        
        // 2. Check session
        if (Session::has('locale') && in_array(Session::get('locale'), $supportedLocales)) {
            return Session::get('locale');
        }
        
        // 3. Check user preference (if authenticated)
        if (auth()->check() && auth()->user()->locale) {
            if (in_array(auth()->user()->locale, $supportedLocales)) {
                return auth()->user()->locale;
            }
        }
        
        // 4. Check browser language
        $browserLocale = $this->getBrowserLocale($request, $supportedLocales);
        if ($browserLocale) {
            return $browserLocale;
        }
        
        // 5. Default to application default
        return config('app.locale', 'en');
    }
    
    /**
     * Get browser locale
     */
    private function getBrowserLocale(Request $request, array $supportedLocales): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // Parse Accept-Language header
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $locale = trim($parts[0]);
            $quality = 1.0;
            
            if (isset($parts[1]) && str_starts_with($parts[1], 'q=')) {
                $quality = (float) substr($parts[1], 2);
            }
            
            $languages[$locale] = $quality;
        }
        
        // Sort by quality
        arsort($languages);
        
        // Find first supported locale
        foreach ($languages as $locale => $quality) {
            // Check exact match
            if (in_array($locale, $supportedLocales)) {
                return $locale;
            }
            
            // Check language code match (e.g., 'en-US' -> 'en')
            $langCode = substr($locale, 0, 2);
            if (in_array($langCode, $supportedLocales)) {
                return $langCode;
            }
        }
        
        return null;
    }
}
