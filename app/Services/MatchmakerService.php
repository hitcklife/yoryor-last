<?php

namespace App\Services;

use App\Models\User;
use App\Models\Matchmaker;
use App\Models\MatchmakerClient;
use App\Models\MatchmakerIntroduction;
use App\Models\MatchmakerService as MatchmakerServiceModel;
use App\Models\MatchmakerConsultation;
use App\Models\MatchmakerReview;
use App\Services\AI\CompatibilityService;
use App\Services\AI\OpenAIService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MatchmakerService
{
    private CompatibilityService $compatibilityService;
    private OpenAIService $openAIService;
    private NotificationService $notificationService;

    public function __construct(
        CompatibilityService $compatibilityService,
        OpenAIService $openAIService,
        NotificationService $notificationService
    ) {
        $this->compatibilityService = $compatibilityService;
        $this->openAIService = $openAIService;
        $this->notificationService = $notificationService;
    }

    /**
     * Register a new matchmaker
     */
    public function registerMatchmaker(User $user, array $data): array
    {
        try {
            // Check if already a matchmaker
            if ($user->is_matchmaker) {
                return [
                    'success' => false,
                    'error' => 'User is already registered as a matchmaker',
                ];
            }

            $matchmaker = DB::transaction(function () use ($user, $data) {
                // Create matchmaker profile
                $matchmaker = Matchmaker::create([
                    'user_id' => $user->id,
                    'business_name' => $data['business_name'] ?? null,
                    'bio' => $data['bio'],
                    'phone' => $data['phone'] ?? null,
                    'website' => $data['website'] ?? null,
                    'specializations' => $data['specializations'] ?? [],
                    'languages' => $data['languages'] ?? [],
                    'years_experience' => $data['years_experience'] ?? 0,
                    'verification_status' => 'pending',
                ]);

                // Update user flag
                $user->update(['is_matchmaker' => true]);

                // Create default services
                $this->createDefaultServices($matchmaker);

                return $matchmaker;
            });

            // Send verification request to admin
            $this->notificationService->notifyAdminOfMatchmakerRegistration($matchmaker);

            return [
                'success' => true,
                'matchmaker' => $matchmaker,
                'message' => 'Matchmaker registration submitted for verification',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to register matchmaker', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to register as matchmaker',
            ];
        }
    }

    /**
     * Create default service packages for matchmaker
     */
    private function createDefaultServices(Matchmaker $matchmaker): void
    {
        $defaultServices = [
            [
                'name' => 'Initial Consultation',
                'description' => 'One-on-one consultation to understand your preferences and goals',
                'price' => 50,
                'currency' => 'USD',
                'duration_unit' => 'days',
                'duration_value' => 1,
                'max_introductions' => 0,
                'features' => ['30-minute video call', 'Personality assessment', 'Match criteria discussion'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic Package',
                'description' => 'Personalized matchmaking with up to 3 introductions',
                'price' => 299,
                'currency' => 'USD',
                'duration_unit' => 'months',
                'duration_value' => 1,
                'max_introductions' => 3,
                'features' => [
                    'Profile optimization',
                    'Up to 3 curated matches',
                    'Introduction facilitation',
                    'Basic date coaching',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium Package',
                'description' => 'Comprehensive matchmaking service with ongoing support',
                'price' => 999,
                'currency' => 'USD',
                'duration_unit' => 'months',
                'duration_value' => 3,
                'max_introductions' => 10,
                'features' => [
                    'Full profile makeover',
                    'Up to 10 curated matches',
                    'Background checks',
                    'Date coaching and feedback',
                    'Relationship counseling',
                    'Priority support',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($defaultServices as $service) {
            MatchmakerServiceModel::create([
                'matchmaker_id' => $matchmaker->id,
                ...$service,
            ]);
        }
    }

    /**
     * Find suitable matchmakers for a user
     */
    public function findMatchmakers(User $user, array $filters = []): array
    {
        $query = Matchmaker::where('is_active', true)
            ->where('verification_status', 'verified');

        // Filter by specialization
        if (!empty($filters['specialization'])) {
            $query->whereJsonContains('specializations', $filters['specialization']);
        }

        // Filter by language
        if (!empty($filters['language'])) {
            $query->whereJsonContains('languages', $filters['language']);
        }

        // Filter by rating
        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        // Filter by experience
        if (!empty($filters['min_experience'])) {
            $query->where('years_experience', '>=', $filters['min_experience']);
        }

        // Sort by rating and success rate
        $query->orderByDesc('rating')
              ->orderByDesc('success_rate');

        $matchmakers = $query->with(['user.profile', 'services' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }])->paginate(10);

        return [
            'matchmakers' => $matchmakers->items(),
            'pagination' => [
                'total' => $matchmakers->total(),
                'per_page' => $matchmakers->perPage(),
                'current_page' => $matchmakers->currentPage(),
                'last_page' => $matchmakers->lastPage(),
            ],
        ];
    }

    /**
     * Hire a matchmaker
     */
    public function hireMatchmaker(User $client, Matchmaker $matchmaker, int $serviceId, array $preferences): array
    {
        try {
            // Check if already a client
            if ($matchmaker->clients()->where('client_id', $client->id)->where('status', 'active')->exists()) {
                return [
                    'success' => false,
                    'error' => 'You are already an active client of this matchmaker',
                ];
            }

            $service = $matchmaker->services()->find($serviceId);
            if (!$service || !$service->is_active) {
                return [
                    'success' => false,
                    'error' => 'Invalid service selected',
                ];
            }

            $clientRelation = DB::transaction(function () use ($client, $matchmaker, $service, $preferences) {
                // Calculate contract dates
                $startDate = now();
                $endDate = match ($service->duration_unit) {
                    'days' => $startDate->copy()->addDays($service->duration_value),
                    'weeks' => $startDate->copy()->addWeeks($service->duration_value),
                    'months' => $startDate->copy()->addMonths($service->duration_value),
                    default => $startDate->copy()->addMonths(1),
                };

                // Create client relationship
                $relation = MatchmakerClient::create([
                    'matchmaker_id' => $matchmaker->id,
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'status' => 'active',
                    'goals' => $preferences['goals'] ?? null,
                    'preferences' => $preferences,
                    'contract_start_date' => $startDate,
                    'contract_end_date' => $endDate,
                ]);

                // Update client preferences
                $client->update([
                    'prefers_matchmaker' => true,
                    'assigned_matchmaker_id' => $matchmaker->id,
                ]);

                // Update matchmaker stats
                $matchmaker->increment('total_clients');

                return $relation;
            });

            // Send notifications
            $this->notificationService->notifyMatchmakerOfNewClient($matchmaker, $client, $service);
            $this->notificationService->notifyClientOfMatchmakerHire($client, $matchmaker, $service);

            // Schedule initial consultation
            $this->scheduleInitialConsultation($client, $matchmaker);

            return [
                'success' => true,
                'client_relation' => $clientRelation,
                'message' => 'Successfully hired matchmaker',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to hire matchmaker', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'matchmaker_id' => $matchmaker->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to hire matchmaker',
            ];
        }
    }

    /**
     * Create a match introduction
     */
    public function createIntroduction(
        Matchmaker $matchmaker,
        User $client,
        User $suggestedUser,
        string $message,
        string $compatibilityNotes
    ): array {
        try {
            // Verify client relationship
            $clientRelation = $matchmaker->clients()
                ->where('client_id', $client->id)
                ->where('status', 'active')
                ->first();

            if (!$clientRelation) {
                return [
                    'success' => false,
                    'error' => 'Client relationship not found or inactive',
                ];
            }

            // Check introduction limit
            if ($clientRelation->service && 
                $clientRelation->service->max_introductions &&
                $clientRelation->introductions_made >= $clientRelation->service->max_introductions) {
                return [
                    'success' => false,
                    'error' => 'Introduction limit reached for this service',
                ];
            }

            // Calculate compatibility
            $compatibility = $this->compatibilityService->calculateCompatibility($client, $suggestedUser);

            $introduction = DB::transaction(function () use (
                $matchmaker,
                $client,
                $suggestedUser,
                $message,
                $compatibilityNotes,
                $compatibility,
                $clientRelation
            ) {
                // Create introduction
                $intro = MatchmakerIntroduction::create([
                    'matchmaker_id' => $matchmaker->id,
                    'client_id' => $client->id,
                    'suggested_user_id' => $suggestedUser->id,
                    'introduction_message' => $message,
                    'compatibility_notes' => $compatibilityNotes,
                    'compatibility_score' => $compatibility['overall_score'],
                ]);

                // Update client stats
                $clientRelation->increment('introductions_made');

                return $intro;
            });

            // Send notifications
            $this->notificationService->notifyClientOfIntroduction($client, $suggestedUser, $matchmaker, $introduction);
            $this->notificationService->notifySuggestedUserOfIntroduction($suggestedUser, $client, $matchmaker, $introduction);

            return [
                'success' => true,
                'introduction' => $introduction,
                'compatibility' => $compatibility,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create introduction', [
                'error' => $e->getMessage(),
                'matchmaker_id' => $matchmaker->id,
                'client_id' => $client->id,
                'suggested_user_id' => $suggestedUser->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create introduction',
            ];
        }
    }

    /**
     * Respond to introduction
     */
    public function respondToIntroduction(
        MatchmakerIntroduction $introduction,
        User $user,
        string $response
    ): bool {
        try {
            $isClient = $introduction->client_id === $user->id;
            $isSuggested = $introduction->suggested_user_id === $user->id;

            if (!$isClient && !$isSuggested) {
                return false;
            }

            if ($isClient) {
                $introduction->update([
                    'client_response' => $response,
                    'client_responded_at' => now(),
                ]);
            } else {
                $introduction->update([
                    'suggested_user_response' => $response,
                    'suggested_user_responded_at' => now(),
                ]);
            }

            // Check if both responded positively
            if ($introduction->client_response === 'interested' &&
                $introduction->suggested_user_response === 'interested') {
                
                // Create a match
                $this->createMatchFromIntroduction($introduction);
                
                // Update matchmaker stats
                $introduction->matchmaker->increment('successful_matches');
                
                // Update client relation stats
                $clientRelation = MatchmakerClient::where('matchmaker_id', $introduction->matchmaker_id)
                    ->where('client_id', $introduction->client_id)
                    ->first();
                    
                if ($clientRelation) {
                    $clientRelation->increment('successful_matches');
                }
            }

            // Notify matchmaker of response
            $this->notificationService->notifyMatchmakerOfIntroductionResponse(
                $introduction->matchmaker,
                $introduction,
                $user,
                $response
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to respond to introduction', [
                'error' => $e->getMessage(),
                'introduction_id' => $introduction->id,
                'user_id' => $user->id,
            ]);

            return false;
        }
    }

    /**
     * Schedule initial consultation
     */
    private function scheduleInitialConsultation(User $client, Matchmaker $matchmaker): void
    {
        try {
            // Find next available slot
            $nextSlot = $this->findNextAvailableSlot($matchmaker);
            
            if ($nextSlot) {
                MatchmakerConsultation::create([
                    'matchmaker_id' => $matchmaker->id,
                    'user_id' => $client->id,
                    'scheduled_at' => $nextSlot,
                    'duration_minutes' => 30,
                    'type' => 'initial',
                    'status' => 'scheduled',
                    'agenda' => 'Initial consultation to discuss your preferences and matchmaking goals',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to schedule consultation', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'matchmaker_id' => $matchmaker->id,
            ]);
        }
    }

    /**
     * Find next available slot for matchmaker
     */
    private function findNextAvailableSlot(Matchmaker $matchmaker): ?Carbon
    {
        $availability = $matchmaker->availability()
            ->where('is_available', true)
            ->get();

        if ($availability->isEmpty()) {
            return null;
        }

        // Start from tomorrow
        $date = now()->addDay()->startOfDay();
        
        for ($i = 0; $i < 14; $i++) { // Check next 2 weeks
            $dayOfWeek = strtolower($date->format('l'));
            $dayAvailability = $availability->firstWhere('day_of_week', $dayOfWeek);
            
            if ($dayAvailability) {
                $slotTime = $date->copy()->setTimeFromTimeString($dayAvailability->start_time);
                
                // Check if slot is not already booked
                $isBooked = MatchmakerConsultation::where('matchmaker_id', $matchmaker->id)
                    ->where('scheduled_at', $slotTime)
                    ->where('status', '!=', 'cancelled')
                    ->exists();
                
                if (!$isBooked && $slotTime->isFuture()) {
                    return $slotTime;
                }
            }
            
            $date->addDay();
        }

        return null;
    }

    /**
     * Create match from successful introduction
     */
    private function createMatchFromIntroduction(MatchmakerIntroduction $introduction): void
    {
        try {
            // Create mutual matches
            \App\Models\MatchModel::create([
                'user_id' => $introduction->client_id,
                'matched_user_id' => $introduction->suggested_user_id,
                'matched_at' => now(),
            ]);

            \App\Models\MatchModel::create([
                'user_id' => $introduction->suggested_user_id,
                'matched_user_id' => $introduction->client_id,
                'matched_at' => now(),
            ]);

            // Create chat
            $chat = \App\Models\Chat::create([
                'user_id_1' => min($introduction->client_id, $introduction->suggested_user_id),
                'user_id_2' => max($introduction->client_id, $introduction->suggested_user_id),
                'created_by' => $introduction->matchmaker->user_id,
            ]);

            // Add users to chat
            $chat->users()->attach([
                $introduction->client_id,
                $introduction->suggested_user_id,
            ]);

            // Update introduction
            $introduction->update([
                'outcome' => 'successful',
                'outcome_notes' => 'Match created successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create match from introduction', [
                'error' => $e->getMessage(),
                'introduction_id' => $introduction->id,
            ]);
        }
    }

    /**
     * Get matchmaker dashboard stats
     */
    public function getMatchmakerStats(Matchmaker $matchmaker): array
    {
        $activeClients = $matchmaker->clients()->where('status', 'active')->count();
        $totalIntroductions = $matchmaker->introductions()->count();
        $pendingResponses = $matchmaker->introductions()
            ->where('client_response', 'pending')
            ->orWhere('suggested_user_response', 'pending')
            ->count();
        
        $thisMonthIntroductions = $matchmaker->introductions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $thisMonthSuccessful = $matchmaker->introductions()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('outcome', 'successful')
            ->count();

        $upcomingConsultations = $matchmaker->consultations()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->count();

        return [
            'active_clients' => $activeClients,
            'total_clients' => $matchmaker->total_clients,
            'successful_matches' => $matchmaker->successful_matches,
            'success_rate' => $matchmaker->success_rate,
            'total_introductions' => $totalIntroductions,
            'pending_responses' => $pendingResponses,
            'this_month' => [
                'introductions' => $thisMonthIntroductions,
                'successful' => $thisMonthSuccessful,
                'success_rate' => $thisMonthIntroductions > 0 
                    ? round(($thisMonthSuccessful / $thisMonthIntroductions) * 100, 2) 
                    : 0,
            ],
            'upcoming_consultations' => $upcomingConsultations,
            'rating' => $matchmaker->rating,
            'reviews_count' => $matchmaker->reviews_count,
        ];
    }

    /**
     * Leave review for matchmaker
     */
    public function leaveReview(
        User $user,
        Matchmaker $matchmaker,
        int $rating,
        ?string $review,
        bool $wouldRecommend
    ): bool {
        try {
            // Check if user was a client
            $wasClient = $matchmaker->clients()
                ->where('client_id', $user->id)
                ->exists();

            MatchmakerReview::updateOrCreate(
                [
                    'matchmaker_id' => $matchmaker->id,
                    'user_id' => $user->id,
                ],
                [
                    'rating' => $rating,
                    'review' => $review,
                    'would_recommend' => $wouldRecommend,
                    'is_verified_client' => $wasClient,
                ]
            );

            // Update matchmaker rating
            $this->updateMatchmakerRating($matchmaker);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to leave review', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'matchmaker_id' => $matchmaker->id,
            ]);

            return false;
        }
    }

    /**
     * Update matchmaker rating
     */
    private function updateMatchmakerRating(Matchmaker $matchmaker): void
    {
        $avgRating = $matchmaker->reviews()->avg('rating');
        $reviewsCount = $matchmaker->reviews()->count();
        
        $matchmaker->update([
            'rating' => round($avgRating, 2),
            'reviews_count' => $reviewsCount,
        ]);
    }
}