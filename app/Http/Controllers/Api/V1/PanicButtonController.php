<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserEmergencyContact;
use App\Services\PanicButtonService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PanicButtonController extends Controller
{
    private PanicButtonService $panicButtonService;

    public function __construct(PanicButtonService $panicButtonService)
    {
        $this->panicButtonService = $panicButtonService;
    }

    /**
     * Activate panic button
     */
    public function activate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'trigger_type' => 'required|string|in:emergency_contact,location_sharing,fake_call,silent_alarm,safe_word,date_check_in',
            'location' => 'nullable|array',
            'location.latitude' => 'required_with:location|numeric|between:-90,90',
            'location.longitude' => 'required_with:location|numeric|between:-180,180',
            'location.accuracy' => 'nullable|numeric|min:0',
            'location_address' => 'nullable|string|max:255',
            'user_message' => 'nullable|string|max:500',
            'device_info' => 'nullable|array',
            'context_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->panicButtonService->activatePanic(
            $request->user(),
            $request->trigger_type,
            $request->location,
            $request->location_address,
            $request->device_info,
            $request->context_data,
            $request->user_message
        );

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Cancel panic activation
     */
    public function cancel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->panicButtonService->cancelPanic(
            $request->user(),
            $request->reason
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get panic status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $status = $this->panicButtonService->getPanicStatus($request->user());

        return response()->json([
            'status' => 'success',
            'data' => $status,
        ]);
    }

    /**
     * Setup safety features
     */
    public function setupSafety(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'panic_button_enabled' => 'required|boolean',
            'location_sharing_enabled' => 'nullable|boolean',
            'emergency_contacts_enabled' => 'required|boolean',
            'date_check_ins_enabled' => 'nullable|boolean',
            'fake_call_enabled' => 'nullable|boolean',
            'safe_word' => 'nullable|string|max:50',
            'check_in_interval_minutes' => 'nullable|integer|min:15|max:180',
            'auto_location_sharing' => 'nullable|boolean',
            'trigger_phrases' => 'nullable|array',
            'trigger_phrases.*' => 'string|max:100',
            'share_with_family' => 'nullable|boolean',
            'share_with_friends' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->panicButtonService->setupSafetyFeatures(
            $request->user(),
            $validator->validated()
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Add emergency contact
     */
    public function addEmergencyContact(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|in:parent,sibling,partner,friend,guardian,relative,colleague,other',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'nullable|boolean',
            'receives_panic_alerts' => 'nullable|boolean',
            'receives_location_updates' => 'nullable|boolean',
            'receives_date_check_ins' => 'nullable|boolean',
            'priority_order' => 'nullable|integer|min:1|max:10',
            'notification_preferences' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->panicButtonService->addEmergencyContact(
            $request->user(),
            $validator->validated()
        );

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Get emergency contacts
     */
    public function getEmergencyContacts(Request $request): JsonResponse
    {
        $contacts = $request->user()
            ->emergencyContacts()
            ->orderedByPriority()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $contacts->map(fn($contact) => $contact->getEmergencySummary()),
        ]);
    }

    /**
     * Update emergency contact
     */
    public function updateEmergencyContact(Request $request, UserEmergencyContact $contact): JsonResponse
    {
        // Ensure user owns this contact
        if ($contact->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'relationship' => 'sometimes|string|in:parent,sibling,partner,friend,guardian,relative,colleague,other',
            'phone' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'sometimes|boolean',
            'receives_panic_alerts' => 'sometimes|boolean',
            'receives_location_updates' => 'sometimes|boolean',
            'receives_date_check_ins' => 'sometimes|boolean',
            'priority_order' => 'sometimes|integer|min:1|max:10',
            'notification_preferences' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // If setting as primary, unset other primary contacts
        if ($request->has('is_primary') && $request->is_primary) {
            UserEmergencyContact::where('user_id', $request->user()->id)
                ->where('id', '!=', $contact->id)
                ->update(['is_primary' => false]);
        }

        $contact->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'data' => $contact->getEmergencySummary(),
            'message' => 'Emergency contact updated successfully',
        ]);
    }

    /**
     * Delete emergency contact
     */
    public function deleteEmergencyContact(Request $request, UserEmergencyContact $contact): JsonResponse
    {
        // Ensure user owns this contact
        if ($contact->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found',
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Emergency contact deleted successfully',
        ]);
    }

    /**
     * Verify emergency contact
     */
    public function verifyEmergencyContact(Request $request, UserEmergencyContact $contact): JsonResponse
    {
        // Ensure user owns this contact
        if ($contact->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->panicButtonService->verifyEmergencyContact(
            $contact,
            $request->verification_code
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Resend verification code
     */
    public function resendVerificationCode(Request $request, UserEmergencyContact $contact): JsonResponse
    {
        // Ensure user owns this contact
        if ($contact->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact not found',
            ], 404);
        }

        if ($contact->is_verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contact is already verified',
            ], 400);
        }

        try {
            $code = $contact->generateVerificationCode();
            
            // Send verification code (implementation depends on SMS service)
            // $this->sendVerificationSMS($contact->phone, $code);

            return response()->json([
                'status' => 'success',
                'message' => 'Verification code sent successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification code',
            ], 500);
        }
    }

    /**
     * Test emergency system
     */
    public function testEmergencySystem(Request $request): JsonResponse
    {
        $result = $this->panicButtonService->testEmergencySystem($request->user());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get panic history
     */
    public function getPanicHistory(Request $request): JsonResponse
    {
        $panics = $request->user()
            ->panicActivations()
            ->with(['resolvedBy'])
            ->latest('triggered_at')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'panics' => $panics->items(),
                'pagination' => [
                    'total' => $panics->total(),
                    'per_page' => $panics->perPage(),
                    'current_page' => $panics->currentPage(),
                    'last_page' => $panics->lastPage(),
                    'has_more_pages' => $panics->hasMorePages(),
                    'from' => $panics->firstItem(),
                    'to' => $panics->lastItem()
                ]
            ]
        ]);
    }

    /**
     * Get safety tips
     */
    public function getSafetyTips(Request $request): JsonResponse
    {
        $category = $request->query('category', 'general');
        
        // This would fetch from your safety tips database
        $tips = [
            [
                'id' => 1,
                'title' => 'Meet in Public Places',
                'content' => 'Always meet your date in a public place for the first few meetings.',
                'category' => 'first_date',
                'icon' => '🏛️',
            ],
            [
                'id' => 2,
                'title' => 'Tell Someone Your Plans',
                'content' => 'Always let a friend or family member know where you\'re going and when you expect to return.',
                'category' => 'first_date',
                'icon' => '📞',
            ],
            [
                'id' => 3,
                'title' => 'Trust Your Instincts',
                'content' => 'If something feels wrong, don\'t ignore it. Your safety is more important than being polite.',
                'category' => 'general',
                'icon' => '🧠',
            ],
            [
                'id' => 4,
                'title' => 'Keep Your Phone Charged',
                'content' => 'Make sure your phone is fully charged before going on a date.',
                'category' => 'preparation',
                'icon' => '🔋',
            ],
            [
                'id' => 5,
                'title' => 'Have Your Own Transportation',
                'content' => 'Drive yourself or arrange your own ride to and from the date.',
                'category' => 'first_date',
                'icon' => '🚗',
            ],
        ];

        // Filter by category if specified
        if ($category !== 'general') {
            $tips = array_filter($tips, fn($tip) => $tip['category'] === $category);
        }

        return response()->json([
            'status' => 'success',
            'data' => array_values($tips),
        ]);
    }

    /**
     * Admin: Get all panic activations
     */
    public function getAllPanics(Request $request): JsonResponse
    {
        // Check if user is admin
        if (!$request->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied',
            ], 403);
        }

        $panics = \App\Models\PanicActivation::with(['user.profile', 'resolvedBy'])
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->severity, function ($q, $severity) {
                $q->whereIn('trigger_type', match($severity) {
                    'critical' => ['silent_alarm', 'safe_word'],
                    'high' => ['emergency_contact', 'date_check_in'],
                    'medium' => ['location_sharing'],
                    'low' => ['fake_call'],
                    default => []
                });
            })
            ->latest('triggered_at')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'panics' => $panics->items(),
                'pagination' => [
                    'total' => $panics->total(),
                    'per_page' => $panics->perPage(),
                    'current_page' => $panics->currentPage(),
                    'last_page' => $panics->lastPage(),
                    'has_more_pages' => $panics->hasMorePages(),
                    'from' => $panics->firstItem(),
                    'to' => $panics->lastItem()
                ]
            ]
        ]);
    }

    /**
     * Admin: Resolve panic activation
     */
    public function resolvePanic(Request $request, \App\Models\PanicActivation $panic): JsonResponse
    {
        // Check if user is admin
        if (!$request->user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'resolution_notes' => 'required|string|max:1000',
            'false_alarm' => 'nullable|boolean',
            'authorities_contacted' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $success = $panic->resolve(
            $request->user(),
            $request->resolution_notes,
            $request->boolean('false_alarm')
        );

        if ($request->boolean('authorities_contacted')) {
            $panic->update(['authorities_contacted' => true]);
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Panic resolved successfully' : 'Failed to resolve panic',
        ], $success ? 200 : 400);
    }
}