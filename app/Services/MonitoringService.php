<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class MonitoringService
{
    /**
     * Report an exception to the monitoring service
     *
     * @param Throwable $exception
     * @param array $context
     * @return bool
     */
    public static function reportException(Throwable $exception, array $context = []): bool
    {
        try {
            // Log the exception using our structured logging
            LoggingService::error($exception->getMessage(), [
                'exception' => [
                    'class' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ],
                'context' => $context
            ]);

            // If we're in production, send the exception to external monitoring services
            if (app()->environment('production')) {
                self::notifyExternalServices($exception, $context);
            }

            return true;
        } catch (Throwable $e) {
            // If something goes wrong with reporting, log it but don't throw
            // This prevents a monitoring error from causing more application errors
            Log::error('Failed to report exception to monitoring service', [
                'error' => $e->getMessage(),
                'original_exception' => $exception->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Report a critical event to the monitoring service
     *
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function reportCritical(string $message, array $context = []): bool
    {
        try {
            // Log the critical event
            LoggingService::critical($message, $context);

            // If we're in production, send the event to external monitoring services
            if (app()->environment('production')) {
                self::notifyExternalServices(null, array_merge(['message' => $message], $context));
            }

            return true;
        } catch (Throwable $e) {
            // If something goes wrong with reporting, log it but don't throw
            Log::error('Failed to report critical event to monitoring service', [
                'error' => $e->getMessage(),
                'original_message' => $message
            ]);

            return false;
        }
    }

    /**
     * Notify external monitoring services
     *
     * @param Throwable|null $exception
     * @param array $context
     * @return void
     */
    private static function notifyExternalServices(?Throwable $exception, array $context = []): void
    {
        // Example: Send to Sentry
        if (config('services.sentry.dsn')) {
            self::notifySentry($exception, $context);
        }

        // Example: Send to Slack
        if (config('services.slack.webhook_url')) {
            self::notifySlack($exception, $context);
        }

        // Example: Send to custom monitoring API
        if (config('services.monitoring.api_url')) {
            self::notifyCustomApi($exception, $context);
        }
    }

    /**
     * Notify Sentry
     *
     * @param Throwable|null $exception
     * @param array $context
     * @return void
     */
    private static function notifySentry(?Throwable $exception, array $context = []): void
    {
        // In a real implementation, you would use the Sentry SDK
        // This is just a placeholder for demonstration
        if ($exception) {
            \Sentry\captureException($exception);
        } else {
            \Sentry\captureMessage($context['message'] ?? 'Critical event', \Sentry\Severity::critical());
        }
    }

    /**
     * Notify Slack
     *
     * @param Throwable|null $exception
     * @param array $context
     * @return void
     */
    private static function notifySlack(?Throwable $exception, array $context = []): void
    {
        $webhookUrl = config('services.slack.webhook_url');

        $payload = [
            'text' => $exception
                ? 'Exception: ' . $exception->getMessage()
                : 'Critical Event: ' . ($context['message'] ?? 'No message provided'),
            'attachments' => [
                [
                    'color' => 'danger',
                    'fields' => [
                        [
                            'title' => 'Environment',
                            'value' => app()->environment(),
                            'short' => true
                        ],
                        [
                            'title' => 'Time',
                            'value' => now()->toDateTimeString(),
                            'short' => true
                        ]
                    ]
                ]
            ]
        ];

        if ($exception) {
            $payload['attachments'][0]['fields'][] = [
                'title' => 'Exception',
                'value' => get_class($exception),
                'short' => true
            ];

            $payload['attachments'][0]['fields'][] = [
                'title' => 'Location',
                'value' => $exception->getFile() . ':' . $exception->getLine(),
                'short' => false
            ];
        }

        Http::post($webhookUrl, $payload);
    }

    /**
     * Notify custom monitoring API
     *
     * @param Throwable|null $exception
     * @param array $context
     * @return void
     */
    private static function notifyCustomApi(?Throwable $exception, array $context = []): void
    {
        $apiUrl = config('services.monitoring.api_url');
        $apiKey = config('services.monitoring.api_key');

        $payload = [
            'api_key' => $apiKey,
            'environment' => app()->environment(),
            'timestamp' => now()->toIso8601String(),
            'context' => $context
        ];

        if ($exception) {
            $payload['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => array_slice($exception->getTrace(), 0, 10)
            ];
        } else {
            $payload['message'] = $context['message'] ?? 'Critical event';
            $payload['level'] = 'critical';
        }

        Http::post($apiUrl, $payload);
    }
}
