<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Matchmaker;
use App\Models\MatchmakerIntroduction;
use App\Services\MatchmakerService;
use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MatchmakerController extends Controller
{
    private MatchmakerService $matchmakerService;
    private CacheService $cacheService;

    public function __construct(MatchmakerService $matchmakerService, CacheService $cacheService)
    {
        $this->matchmakerService = $matchmakerService;
        $this->cacheService = $cacheService;
    }

    /**
     * Register as a matchmaker
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Enhanced validation using ValidationService
            $validated = ValidationService::validateRequest($request, [
                'bio' => 'required|string|min:50|max:2000',
                'business_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'specializations' => 'nullable|array',
                'specializations.*' => 'string|in:traditional,modern,religious,professional,international,senior,lgbtq',
                'languages' => 'nullable|array',
                'languages.*' => 'string|max:50',
                'years_experience' => 'nullable|integer|min:0|max:50',
            ], [
                'bio.required' => 'Bio is required',
                'bio.min' => 'Bio must be at least 50 characters',
                'bio.max' => 'Bio cannot exceed 2000 characters',
                'website.url' => 'Website must be a valid URL',
                'specializations.*.in' => 'Invalid specialization type',
                'years_experience.min' => 'Years of experience cannot be negative'
            ]);

            // Check if user is already a matchmaker
            $error = ErrorHandlingService::validateBusinessLogic(
                !$user->matchmaker,
                'You are already registered as a matchmaker',
                ErrorHandlingService::ERROR_CODES['DUPLICATE_ENTRY']
            );
            if ($error) return $error;

            $result = $this->matchmakerService->registerMatchmaker($user, $validated);

            // Clear user cache
            $this->cacheService->invalidateUserCaches($user->id);

            return ErrorHandlingService::successResponse(
                $result,
                $result['success'] ? 'Matchmaker registration successful' : 'Failed to register as matchmaker',
                $result['success'] ? 201 : 400
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'matchmaker_registration');
        }
    }

    /**
     * Get list of available matchmakers
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'specialization' => 'nullable|string',
            'language' => 'nullable|string',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'min_experience' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid filters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->matchmakerService->findMatchmakers(
            $request->user(),
            $validator->validated()
        );

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    /**
     * Get matchmaker profile
     */
    public function show(Matchmaker $matchmaker): JsonResponse
    {
        $matchmaker->load([
            'user.profile',
            'services' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'reviews' => function ($q) {
                $q->with('user.profile')->latest()->limit(10);
            }
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'matchmaker' => $matchmaker,
                'stats' => $this->matchmakerService->getMatchmakerStats($matchmaker),
            ],
        ]);
    }

    /**
     * Hire a matchmaker
     */
    public function hire(Request $request, Matchmaker $matchmaker): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer|exists:matchmaker_services,id',
            'goals' => 'nullable|string|max:1000',
            'preferences' => 'required|array',
            'preferences.age_min' => 'nullable|integer|min:18|max:100',
            'preferences.age_max' => 'nullable|integer|min:18|max:100',
            'preferences.location_preference' => 'nullable|string|max:255',
            'preferences.education_level' => 'nullable|string',
            'preferences.occupation' => 'nullable|string|max:255',
            'preferences.religion_importance' => 'nullable|string|in:very_important,important,somewhat_important,not_important',
            'preferences.family_plans' => 'nullable|string|in:want_children,have_children,no_children,undecided',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->matchmakerService->hireMatchmaker(
            $request->user(),
            $matchmaker,
            $request->service_id,
            $validator->validated()['preferences']
        );

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Get user's matchmaker interactions
     */
    public function myInteractions(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get hired matchmakers
        $hiredMatchmakers = $user->matchmakerClients()
            ->with(['matchmaker.user.profile', 'service'])
            ->where('status', 'active')
            ->get();

        // Get introductions received
        $introductions = MatchmakerIntroduction::where('client_id', $user->id)
            ->orWhere('suggested_user_id', $user->id)
            ->with([
                'matchmaker.user.profile',
                'client.profile',
                'suggestedUser.profile'
            ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'hired_matchmakers' => $hiredMatchmakers,
                'introductions' => $introductions->items(),
                'pagination' => [
                    'total' => $introductions->total(),
                    'per_page' => $introductions->perPage(),
                    'current_page' => $introductions->currentPage(),
                    'last_page' => $introductions->lastPage(),
                    'has_more_pages' => $introductions->hasMorePages(),
                    'from' => $introductions->firstItem(),
                    'to' => $introductions->lastItem()
                ]
            ],
        ]);
    }

    /**
     * Respond to matchmaker introduction
     */
    public function respondToIntroduction(Request $request, MatchmakerIntroduction $introduction): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string|in:interested,not_interested,met',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid response',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->matchmakerService->respondToIntroduction(
            $introduction,
            $request->user(),
            $request->response
        );

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Response recorded successfully' : 'Failed to record response',
        ], $result ? 200 : 400);
    }

    /**
     * Leave review for matchmaker
     */
    public function leaveReview(Request $request, Matchmaker $matchmaker): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'would_recommend' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->matchmakerService->leaveReview(
            $request->user(),
            $matchmaker,
            $request->rating,
            $request->review,
            $request->would_recommend
        );

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Review submitted successfully' : 'Failed to submit review',
        ], $result ? 200 : 400);
    }

    /**
     * Get matchmaker dashboard (for matchmakers only)
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->is_matchmaker) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. User is not a matchmaker.',
            ], 403);
        }

        $matchmaker = $user->matchmaker;
        if (!$matchmaker) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matchmaker profile not found.',
            ], 404);
        }

        $stats = $this->matchmakerService->getMatchmakerStats($matchmaker);

        // Get recent clients
        $recentClients = $matchmaker->clients()
            ->with(['client.profile', 'service'])
            ->where('status', 'active')
            ->latest()
            ->limit(10)
            ->get();

        // Get pending introductions
        $pendingIntroductions = $matchmaker->introductions()
            ->with(['client.profile', 'suggestedUser.profile'])
            ->where(function ($q) {
                $q->where('client_response', 'pending')
                  ->orWhere('suggested_user_response', 'pending');
            })
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'matchmaker' => $matchmaker,
                'stats' => $stats,
                'recent_clients' => $recentClients,
                'pending_introductions' => $pendingIntroductions,
            ],
        ]);
    }

    /**
     * Create introduction (for matchmakers only)
     */
    public function createIntroduction(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->is_matchmaker) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. User is not a matchmaker.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|integer|exists:users,id',
            'suggested_user_id' => 'required|integer|exists:users,id',
            'introduction_message' => 'nullable|string|max:1000',
            'compatibility_notes' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $matchmaker = $user->matchmaker;
        $client = \App\Models\User::find($request->client_id);
        $suggestedUser = \App\Models\User::find($request->suggested_user_id);

        $result = $this->matchmakerService->createIntroduction(
            $matchmaker,
            $client,
            $suggestedUser,
            $request->introduction_message ?? '',
            $request->compatibility_notes
        );

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Get matchmaker's clients (for matchmakers only)
     */
    public function getClients(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->is_matchmaker) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. User is not a matchmaker.',
            ], 403);
        }

        $matchmaker = $user->matchmaker;
        
        $clients = $matchmaker->clients()
            ->with(['client.profile', 'service'])
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'clients' => $clients->items(),
                'pagination' => [
                    'total' => $clients->total(),
                    'per_page' => $clients->perPage(),
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                    'has_more_pages' => $clients->hasMorePages(),
                    'from' => $clients->firstItem(),
                    'to' => $clients->lastItem()
                ]
            ]
        ]);
    }

    /**
     * Update matchmaker profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->is_matchmaker) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. User is not a matchmaker.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'bio' => 'sometimes|string|min:50|max:2000',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|in:traditional,modern,religious,professional,international,senior,lgbtq',
            'languages' => 'nullable|array',
            'languages.*' => 'string|max:50',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $matchmaker = $user->matchmaker;
        $matchmaker->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $matchmaker->fresh(),
        ]);
    }
}