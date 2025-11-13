<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Get locale from various sources in order of priority
        $locale = $this->getLocale($request);
        
        // Validate locale
        if (!in_array($locale, config('app.available_locales', ['en']))) {
            $locale = config('app.locale', 'en');
        }
        
        // Set the locale
        App::setLocale($locale);
        
        // Store in session for persistence
        Session::put('locale', $locale);
        
        return $next($request);
    }
    
    /**
     * Get locale from various sources
     */
    private function getLocale(Request $request): string
    {
        // 1. From URL parameter
        if ($request->has('lang')) {
            return $request->get('lang');
        }
        
        // 2. From authenticated user preference
        if (auth()->check() && auth()->user()->language_preference) {
            return auth()->user()->language_preference;
        }
        
        // 3. From session
        if (Session::has('locale')) {
            return Session::get('locale');
        }
        
        // 4. From cookie
        if ($request->hasCookie('locale')) {
            return $request->cookie('locale');
        }
        
        // 5. From Accept-Language header
        $headerLocale = $request->getPreferredLanguage(['en', 'uz', 'ru']);
        if ($headerLocale) {
            return $headerLocale;
        }
        
        // 6. Fallback to default
        return config('app.locale', 'en');
    }
}
