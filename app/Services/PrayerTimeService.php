<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPrayerTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PrayerTimeService
{
    private string $apiUrl = 'http://api.aladhan.com/v1/timings';
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get prayer times for a user's location
     */
    public function getPrayerTimes(User $user): array
    {
        $location = $this->getUserLocation($user);
        
        if (!$location) {
            return [
                'success' => false,
                'error' => 'Location not available',
            ];
        }

        $cacheKey = "prayer_times:{$location['latitude']}:{$location['longitude']}:" . now()->format('Y-m-d');
        
        return $this->cacheService->remember($cacheKey, 86400, function () use ($location) {
            return $this->fetchPrayerTimes($location);
        });
    }

    /**
     * Fetch prayer times from API
     */
    private function fetchPrayerTimes(array $location): array
    {
        try {
            $response = Http::get($this->apiUrl, [
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'method' => 2, // Islamic Society of North America
                'school' => 1, // Hanafi
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $timings = $data['data']['timings'];
                
                // Convert to user's timezone
                $timezone = $location['timezone'] ?? 'UTC';
                
                return [
                    'success' => true,
                    'date' => $data['data']['date']['readable'],
                    'hijri_date' => $data['data']['date']['hijri']['date'],
                    'timings' => [
                        'fajr' => $this->convertToTimezone($timings['Fajr'], $timezone),
                        'sunrise' => $this->convertToTimezone($timings['Sunrise'], $timezone),
                        'dhuhr' => $this->convertToTimezone($timings['Dhuhr'], $timezone),
                        'asr' => $this->convertToTimezone($timings['Asr'], $timezone),
                        'maghrib' => $this->convertToTimezone($timings['Maghrib'], $timezone),
                        'isha' => $this->convertToTimezone($timings['Isha'], $timezone),
                    ],
                    'timezone' => $timezone,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch prayer times',
            ];

        } catch (\Exception $e) {
            Log::error('Prayer time API error', [
                'error' => $e->getMessage(),
                'location' => $location,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to fetch prayer times',
            ];
        }
    }

    /**
     * Calculate prayer time compatibility between users
     */
    public function calculatePrayerCompatibility(User $user1, User $user2): array
    {
        $cultural1 = $user1->culturalProfile;
        $cultural2 = $user2->culturalProfile;

        if (!$cultural1 || !$cultural2) {
            return [
                'score' => 50,
                'analysis' => 'Prayer preferences not available',
            ];
        }

        $score = 100;
        $factors = [];

        // Prayer frequency compatibility
        if ($cultural1->prayer_frequency === $cultural2->prayer_frequency) {
            $factors[] = 'Both pray ' . $this->formatPrayerFrequency($cultural1->prayer_frequency);
        } else {
            $diff = abs($this->getPrayerFrequencyLevel($cultural1->prayer_frequency) - 
                       $this->getPrayerFrequencyLevel($cultural2->prayer_frequency));
            $score -= $diff * 15;
            
            if ($diff <= 1) {
                $factors[] = 'Similar prayer commitment levels';
            } else {
                $factors[] = 'Different prayer frequency preferences';
            }
        }

        // Check if both users have prayer time preferences
        $prayerTime1 = $this->getUserPrayerPreferences($user1);
        $prayerTime2 = $this->getUserPrayerPreferences($user2);

        if ($prayerTime1 && $prayerTime2) {
            // Check preferred prayer times alignment
            $alignment = $this->calculateTimeAlignment($prayerTime1, $prayerTime2);
            $score = ($score * 0.7) + ($alignment * 0.3);
            
            if ($alignment >= 80) {
                $factors[] = 'Prayer time preferences align well';
            } elseif ($alignment >= 60) {
                $factors[] = 'Moderate prayer time alignment';
            } else {
                $factors[] = 'Different prayer time preferences';
            }
        }

        // Ramadan observance
        if ($cultural1->observes_ramadan === $cultural2->observes_ramadan) {
            if ($cultural1->observes_ramadan) {
                $factors[] = 'Both observe Ramadan';
            }
        } else {
            $score -= 20;
            $factors[] = 'Different Ramadan observance practices';
        }

        return [
            'score' => max(0, min(100, $score)),
            'analysis' => implode('. ', $factors),
            'recommendations' => $this->getPrayerCompatibilityRecommendations($cultural1, $cultural2),
        ];
    }

    /**
     * Get user's prayer preferences
     */
    public function getUserPrayerPreferences(User $user): ?UserPrayerTime
    {
        return UserPrayerTime::where('user_id', $user->id)->first();
    }

    /**
     * Save user's prayer preferences
     */
    public function savePrayerPreferences(User $user, array $preferences): bool
    {
        try {
            UserPrayerTime::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'fajr_time' => $preferences['fajr_time'] ?? null,
                    'dhuhr_time' => $preferences['dhuhr_time'] ?? null,
                    'asr_time' => $preferences['asr_time'] ?? null,
                    'maghrib_time' => $preferences['maghrib_time'] ?? null,
                    'isha_time' => $preferences['isha_time'] ?? null,
                    'notification_enabled' => $preferences['notification_enabled'] ?? false,
                    'notification_minutes_before' => $preferences['notification_minutes_before'] ?? 15,
                    'preferred_calculation_method' => $preferences['calculation_method'] ?? 'isna',
                    'timezone' => $preferences['timezone'] ?? $user->timezone ?? 'UTC',
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save prayer preferences', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return false;
        }
    }

    /**
     * Get prayer time notifications for user
     */
    public function getUpcomingPrayers(User $user): array
    {
        $prayerTimes = $this->getPrayerTimes($user);
        
        if (!$prayerTimes['success']) {
            return [];
        }

        $upcoming = [];
        $now = now();
        
        foreach ($prayerTimes['timings'] as $prayer => $time) {
            $prayerTime = Carbon::parse($time, $prayerTimes['timezone']);
            
            if ($prayerTime->isAfter($now)) {
                $upcoming[] = [
                    'prayer' => ucfirst($prayer),
                    'time' => $prayerTime->format('h:i A'),
                    'in_minutes' => $now->diffInMinutes($prayerTime),
                    'timestamp' => $prayerTime->timestamp,
                ];
            }
        }

        return array_slice($upcoming, 0, 2); // Return next 2 prayers
    }

    /**
     * Check if users can match based on prayer times
     */
    public function canMatchDuringPrayerTime(User $user1, User $user2): bool
    {
        // Get current prayer windows for both users
        $prayers1 = $this->getCurrentPrayerWindow($user1);
        $prayers2 = $this->getCurrentPrayerWindow($user2);

        // If either user is in prayer time and has strict settings, don't match
        if (($prayers1['in_prayer_window'] && $prayers1['strict_observance']) ||
            ($prayers2['in_prayer_window'] && $prayers2['strict_observance'])) {
            return false;
        }

        return true;
    }

    /**
     * Get current prayer window
     */
    private function getCurrentPrayerWindow(User $user): array
    {
        $prayerTimes = $this->getPrayerTimes($user);
        
        if (!$prayerTimes['success']) {
            return [
                'in_prayer_window' => false,
                'strict_observance' => false,
            ];
        }

        $now = now();
        $cultural = $user->culturalProfile;
        
        // Check if user has strict prayer observance
        $strictObservance = $cultural && 
                           in_array($cultural->prayer_frequency, ['5_times_daily', 'regularly']);

        // Check each prayer time (typically 30-minute window)
        foreach ($prayerTimes['timings'] as $prayer => $time) {
            $prayerTime = Carbon::parse($time, $prayerTimes['timezone']);
            $endTime = $prayerTime->copy()->addMinutes(30);
            
            if ($now->between($prayerTime, $endTime)) {
                return [
                    'in_prayer_window' => true,
                    'current_prayer' => ucfirst($prayer),
                    'strict_observance' => $strictObservance,
                ];
            }
        }

        return [
            'in_prayer_window' => false,
            'strict_observance' => $strictObservance,
        ];
    }

    /**
     * Get user location for prayer times
     */
    private function getUserLocation(User $user): ?array
    {
        // Try to get from profile
        if ($user->profile && $user->profile->latitude && $user->profile->longitude) {
            return [
                'latitude' => $user->profile->latitude,
                'longitude' => $user->profile->longitude,
                'timezone' => $user->timezone ?? $this->getTimezoneFromCoordinates(
                    $user->profile->latitude,
                    $user->profile->longitude
                ),
            ];
        }

        // Try to get from IP address
        if ($user->last_ip_address) {
            return $this->getLocationFromIP($user->last_ip_address);
        }

        // Fallback to default location (Tashkent)
        return [
            'latitude' => 41.2995,
            'longitude' => 69.2401,
            'timezone' => 'Asia/Tashkent',
        ];
    }

    /**
     * Convert prayer time to timezone
     */
    private function convertToTimezone(string $time, string $timezone): string
    {
        try {
            // Remove timezone suffix if present
            $time = preg_replace('/\s*\([^)]*\)/', '', $time);
            
            return Carbon::createFromFormat('H:i', $time, 'UTC')
                        ->setTimezone($timezone)
                        ->format('h:i A');
        } catch (\Exception $e) {
            return $time;
        }
    }

    /**
     * Get prayer frequency level
     */
    private function getPrayerFrequencyLevel(string $frequency): int
    {
        return match ($frequency) {
            '5_times_daily' => 5,
            'regularly' => 4,
            'friday_only' => 3,
            'occasionally' => 2,
            'rarely' => 1,
            'never' => 0,
            default => 2,
        };
    }

    /**
     * Format prayer frequency
     */
    private function formatPrayerFrequency(string $frequency): string
    {
        return match ($frequency) {
            '5_times_daily' => '5 times daily',
            'regularly' => 'regularly',
            'friday_only' => 'on Fridays',
            'occasionally' => 'occasionally',
            'rarely' => 'rarely',
            'never' => 'never',
            default => $frequency,
        };
    }

    /**
     * Calculate time alignment between users
     */
    private function calculateTimeAlignment($prayerTime1, $prayerTime2): float
    {
        if (!$prayerTime1 || !$prayerTime2) {
            return 50;
        }

        $score = 100;
        $prayers = ['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'];
        
        foreach ($prayers as $prayer) {
            $time1 = $prayerTime1->{$prayer . '_time'};
            $time2 = $prayerTime2->{$prayer . '_time'};
            
            if ($time1 && $time2) {
                $diff = abs(Carbon::parse($time1)->diffInMinutes(Carbon::parse($time2)));
                
                if ($diff <= 15) {
                    // Perfect alignment
                } elseif ($diff <= 30) {
                    $score -= 5;
                } elseif ($diff <= 60) {
                    $score -= 10;
                } else {
                    $score -= 15;
                }
            }
        }

        return max(0, $score);
    }

    /**
     * Get prayer compatibility recommendations
     */
    private function getPrayerCompatibilityRecommendations($cultural1, $cultural2): array
    {
        $recommendations = [];

        if ($cultural1->prayer_frequency !== $cultural2->prayer_frequency) {
            $recommendations[] = 'Discuss prayer expectations and find common ground';
        }

        if ($cultural1->observes_ramadan !== $cultural2->observes_ramadan) {
            $recommendations[] = 'Talk about Ramadan observance and fasting practices';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Your prayer practices align well';
        }

        return $recommendations;
    }

    /**
     * Get timezone from coordinates
     */
    private function getTimezoneFromCoordinates(float $lat, float $lng): string
    {
        // This is a simplified implementation
        // In production, use a proper timezone API
        
        // Common timezones for Uzbek diaspora
        if ($lng >= 60 && $lng <= 75 && $lat >= 35 && $lat <= 45) {
            return 'Asia/Tashkent'; // Uzbekistan
        } elseif ($lng >= 25 && $lng <= 40 && $lat >= 35 && $lat <= 42) {
            return 'Europe/Istanbul'; // Turkey
        } elseif ($lng >= 30 && $lng <= 60 && $lat >= 50 && $lat <= 60) {
            return 'Europe/Moscow'; // Russia
        } elseif ($lng >= -10 && $lng <= 30 && $lat >= 40 && $lat <= 60) {
            return 'Europe/Berlin'; // Central Europe
        } elseif ($lng >= -130 && $lng <= -60 && $lat >= 25 && $lat <= 50) {
            return 'America/New_York'; // USA East
        } elseif ($lng >= 50 && $lng <= 60 && $lat >= 20 && $lat <= 30) {
            return 'Asia/Dubai'; // UAE
        }
        
        return 'UTC';
    }

    /**
     * Get location from IP address
     */
    private function getLocationFromIP(string $ip): ?array
    {
        try {
            // You would use a geolocation service here
            // For now, return null
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}