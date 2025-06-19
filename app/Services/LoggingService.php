<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LoggingService
{
    /**
     * Log an emergency message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function emergency(string $message, array $context = []): void
    {
        self::log('emergency', $message, $context);
    }

    /**
     * Log an alert message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function alert(string $message, array $context = []): void
    {
        self::log('alert', $message, $context);
    }

    /**
     * Log a critical message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function critical(string $message, array $context = []): void
    {
        self::log('critical', $message, $context);
    }

    /**
     * Log an error message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    /**
     * Log a notice message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function notice(string $message, array $context = []): void
    {
        self::log('notice', $message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /**
     * Log a debug message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    /**
     * Log a message with the specified level
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        // Add request information to context
        if (request()) {
            $context['request'] = [
                'id' => request()->id ?? uniqid(),
                'ip' => request()->ip(),
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'user_agent' => request()->userAgent(),
            ];

            // Add authenticated user information if available
            if (auth()->check()) {
                $context['user'] = [
                    'id' => auth()->id(),
                    'email' => auth()->user()->email,
                ];
            }
        }

        // Add timestamp
        $context['timestamp'] = now()->toIso8601String();

        // Add environment
        $context['environment'] = app()->environment();

        // Log the message with the enhanced context
        Log::$level($message, $context);
    }
}
