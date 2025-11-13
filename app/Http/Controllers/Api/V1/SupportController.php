<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use App\Models\UserReport;
use App\Models\User;
use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Submit feedback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Enhanced validation using ValidationService
            $validated = ValidationService::validateRequest($request, [
                'feedback_text' => 'required|string|min:10|max:2000',
                'email' => 'nullable|email',
                'category' => 'nullable|string|in:bug,feature,complaint,suggestion,other',
                'rating' => 'nullable|integer|min:1|max:5'
            ], [
                'feedback_text.required' => 'Feedback text is required',
                'feedback_text.min' => 'Feedback must be at least 10 characters',
                'feedback_text.max' => 'Feedback cannot exceed 2000 characters',
                'email.email' => 'Please provide a valid email address',
                'category.in' => 'Invalid feedback category',
                'rating.between' => 'Rating must be between 1 and 5'
            ]);

            $feedback = UserFeedback::create([
                'user_id' => $user?->id,
                'feedback_text' => $validated['feedback_text'],
                'email' => $validated['email'] ?? $user?->email,
                'category' => $validated['category'] ?? 'other',
                'rating' => $validated['rating'] ?? null,
                'submitted_at' => now(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip()
            ]);

            return ErrorHandlingService::successResponse([
                'feedback_id' => $feedback->id,
                'submitted_at' => $feedback->submitted_at
            ], 'Feedback submitted successfully', 201);

        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'submit_feedback');
        }
    }

    /**
     * Report a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reportUser(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'reported_id' => 'required|exists:users,id',
            'reason' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if trying to report self
        if ($user->id == $request->reported_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot report yourself'
            ], 422);
        }

        // Check if already reported
        if (UserReport::hasReported($user->id, $request->reported_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already reported this user'
            ], 422);
        }

        // Create the report
        $report = new UserReport([
            'reporter_id' => $user->id,
            'reported_id' => $request->reported_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        $report->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User reported successfully',
            'data' => $report
        ]);
    }

    /**
     * Get FAQ data
     *
     * @return JsonResponse
     */
    public function getFaq(): JsonResponse
    {
        // In a real application, this would likely come from a database
        // For this example, we'll return a static array
        $faqData = [
            [
                'question' => 'How do I change my password?',
                'answer' => 'You can change your password in the Account Settings section. Go to Settings > Account > Change Password.',
                'category' => 'account',
            ],
            [
                'question' => 'How do I delete my account?',
                'answer' => 'To delete your account, go to Settings > Account > Delete Account. Please note that this action is irreversible.',
                'category' => 'account',
            ],
            [
                'question' => 'How do I block a user?',
                'answer' => 'You can block a user by visiting their profile and tapping the "Block" button, or from the Settings > Blocked Users section.',
                'category' => 'safety',
            ],
            [
                'question' => 'How do I report inappropriate behavior?',
                'answer' => 'You can report a user by visiting their profile and tapping the "Report" button. Please provide as much detail as possible.',
                'category' => 'safety',
            ],
            [
                'question' => 'What are emergency contacts?',
                'answer' => 'Emergency contacts are trusted people who can be notified in case of an emergency. You can add them in Settings > Emergency Contacts.',
                'category' => 'safety',
            ],
            [
                'question' => 'How do I change my notification settings?',
                'answer' => 'You can customize your notification preferences in Settings > Notifications.',
                'category' => 'notifications',
            ],
            [
                'question' => 'How do I change my privacy settings?',
                'answer' => 'You can adjust your privacy settings in Settings > Privacy.',
                'category' => 'privacy',
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $faqData
        ]);
    }
}
