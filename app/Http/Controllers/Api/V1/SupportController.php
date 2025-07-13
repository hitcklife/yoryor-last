<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use App\Models\UserReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    /**
     * Submit feedback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'feedback_text' => 'required|string|min:10',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback = new UserFeedback([
            'feedback_text' => $request->feedback_text,
            'email' => $request->email,
        ]);

        // If user is authenticated, associate the feedback with them
        if ($request->user()) {
            $feedback->user_id = $request->user()->id;
        }

        $feedback->save();

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ]);
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
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if trying to report self
        if ($user->id == $request->reported_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot report yourself'
            ], 422);
        }

        // Check if already reported
        if (UserReport::hasReported($user->id, $request->reported_id)) {
            return response()->json([
                'success' => false,
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
            'success' => true,
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
            'success' => true,
            'data' => $faqData
        ]);
    }
}
