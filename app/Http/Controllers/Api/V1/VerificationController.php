<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use App\Services\VerificationService;
use App\Services\CacheService;
use App\Services\ErrorHandlingService;
use App\Services\ValidationService;
use App\Jobs\ProcessVerificationDocumentsJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    private VerificationService $verificationService;
    private CacheService $cacheService;

    public function __construct(VerificationService $verificationService, CacheService $cacheService)
    {
        $this->verificationService = $verificationService;
        $this->cacheService = $cacheService;
    }

    /**
     * Get user's verification status
     */
    public function getStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            return $this->cacheService->remember(
                "verification_status:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $status = $this->verificationService->getUserVerificationStatus($user);
                    return ErrorHandlingService::successResponse($status, 'Verification status retrieved');
                },
                ["user_{$user->id}_verification"]
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'get_verification_status');
        }
    }

    /**
     * Submit verification request
     */
    public function submitRequest(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Enhanced validation using ValidationService
            $validated = ValidationService::validateRequest($request, [
                'verification_type' => 'required|string|in:identity,photo,employment,education,income,address,social_media,background_check',
                'user_notes' => 'nullable|string|max:1000',
                'submitted_data' => 'required|array',
                'documents' => 'required|array|min:1',
                'documents.*' => 'file|max:10240', // 10MB max per file
            ], [
                'verification_type.required' => 'Verification type is required',
                'verification_type.in' => 'Invalid verification type',
                'documents.required' => 'At least one document is required',
                'documents.*.max' => 'Each document must be less than 10MB'
            ]);

            // Additional validation based on verification type
            $typeValidation = $this->validateByType($request->verification_type, $request->all());
            if (!$typeValidation['success']) {
                return response()->json($typeValidation, 422);
            }

            // Check for existing pending verification
            $existingPending = VerificationRequest::where('user_id', $user->id)
                ->where('verification_type', $request->verification_type)
                ->whereIn('status', ['pending', 'pending_review', 'processing'])
                ->first();

            $error = ErrorHandlingService::validateBusinessLogic(
                !$existingPending,
                'You already have a pending verification request for this type',
                ErrorHandlingService::ERROR_CODES['DUPLICATE_ENTRY']
            );
            if ($error) return $error;

            // Create verification request first (without documents)
            $verificationRequest = VerificationRequest::create([
                'user_id' => $user->id,
                'verification_type' => $request->verification_type,
                'submitted_data' => $request->submitted_data,
                'user_notes' => $request->user_notes,
                'status' => 'processing',
                'submitted_at' => now()
            ]);

            // Prepare uploaded files for background processing
            $uploadedFiles = [];
            foreach ($request->file('documents', []) as $key => $file) {
                $uploadedFiles[] = [
                    'key' => $key,
                    'file' => $file
                ];
            }

            // Dispatch background job for document processing
            ProcessVerificationDocumentsJob::dispatch($verificationRequest, $uploadedFiles)
                ->onQueue('documents');

            // Clear user verification cache
            $this->cacheService->invalidateByTag(["user_{$user->id}_verification"]);

            return ErrorHandlingService::successResponse([
                'verification_request_id' => $verificationRequest->id,
                'status' => 'processing',
                'estimated_processing_time' => '2-5 minutes'
            ], 'Verification request submitted successfully. Documents are being processed.', 201);

        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'submit_verification_request');
        }
    }

    /**
     * Get verification requests for user
     */
    public function getRequests(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Validate pagination parameters
            ValidationService::validatePagination($request);
            
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            return $this->cacheService->remember(
                "verification_requests:{$user->id}:page:{$page}:per:{$perPage}",
                CacheService::TTL_SHORT,
                function() use ($user, $perPage) {
                    $requests = $user->verificationRequests()
                        ->with(['reviewedBy:id,email,first_name,last_name'])
                        ->latest('submitted_at')
                        ->paginate($perPage);

                    return ErrorHandlingService::paginatedResponse($requests, 'verification_requests');
                },
                ["user_{$user->id}_verification"]
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'get_verification_requests');
        }
    }

    /**
     * Get specific verification request
     */
    public function getRequest(Request $request, VerificationRequest $verificationRequest): JsonResponse
    {
        try {
            $user = $request->user();

            // Ensure user owns this request
            $error = ErrorHandlingService::validateBusinessLogic(
                $verificationRequest->user_id === $user->id,
                'Verification request not found',
                ErrorHandlingService::ERROR_CODES['NOT_FOUND']
            );
            if ($error) return $error;

            return $this->cacheService->remember(
                "verification_request:{$verificationRequest->id}:user:{$user->id}",
                CacheService::TTL_SHORT,
                function() use ($verificationRequest) {
                    $verificationRequest->load(['reviewedBy:id,email,first_name,last_name']);
                    return ErrorHandlingService::successResponse($verificationRequest, 'Verification request retrieved');
                },
                ["verification_request_{$verificationRequest->id}", "user_{$user->id}_verification"]
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'get_verification_request');
        }
    }

    /**
     * Get verification requirements for a type
     */
    public function getRequirements(Request $request, string $verificationType): JsonResponse
    {
        $validTypes = ['identity', 'photo', 'employment', 'education', 'income', 'address', 'social_media', 'background_check'];
        
        if (!in_array($verificationType, $validTypes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification type',
            ], 400);
        }

        $badgeType = match ($verificationType) {
            'identity' => 'identity_verified',
            'photo' => 'photo_verified',
            'employment' => 'employment_verified',
            'education' => 'education_verified',
            'income' => 'income_verified',
            'address' => 'address_verified',
            'social_media' => 'social_verified',
            'background_check' => 'background_check',
        };

        $requirements = \App\Models\UserVerifiedBadge::getRequirementsForType($badgeType);
        $verificationRequest = new VerificationRequest(['verification_type' => $verificationType]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'verification_type' => $verificationType,
                'badge_type' => $badgeType,
                'requirements' => $requirements,
                'required_documents' => $verificationRequest->getRequiredDocuments(),
                'form_fields' => $this->getFormFields($verificationType),
            ],
        ]);
    }

    /**
     * Admin: Get pending verification requests
     */
    public function getPendingRequests(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is admin
            $error = ErrorHandlingService::validateBusinessLogic(
                $user->is_admin,
                'Access denied',
                ErrorHandlingService::ERROR_CODES['FORBIDDEN']
            );
            if ($error) return $error;

            // Validate pagination parameters
            ValidationService::validatePagination($request);
            
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);
            $status = $request->input('status', 'pending');

            return $this->cacheService->remember(
                "admin_verification_requests:{$status}:page:{$page}:per:{$perPage}",
                CacheService::TTL_SHORT,
                function() use ($status, $perPage) {
                    $query = VerificationRequest::query()
                        ->with([
                            'user:id,email',
                            'user.profile:id,user_id,first_name,last_name',
                            'reviewedBy:id,email,first_name,last_name'
                        ])
                        ->latest('submitted_at');

                    if ($status !== 'all') {
                        $query->where('status', $status);
                    }

                    $requests = $query->paginate($perPage);
                    return ErrorHandlingService::paginatedResponse($requests, 'verification_requests');
                },
                ["admin_verification_requests"]
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'get_pending_verification_requests');
        }
    }

    /**
     * Admin: Approve verification request
     */
    public function approveRequest(Request $request, VerificationRequest $verificationRequest): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is admin
            $error = ErrorHandlingService::validateBusinessLogic(
                $user->is_admin,
                'Access denied',
                ErrorHandlingService::ERROR_CODES['FORBIDDEN']
            );
            if ($error) return $error;

            // Validate request data
            $validated = ValidationService::validateRequest($request, [
                'feedback' => 'nullable|string|max:1000',
            ], [
                'feedback.max' => 'Feedback cannot exceed 1000 characters'
            ]);

            // Check if verification can be approved
            $error = ErrorHandlingService::validateBusinessLogic(
                in_array($verificationRequest->status, ['pending', 'pending_review']),
                'Verification request cannot be approved in current status',
                ErrorHandlingService::ERROR_CODES['INVALID_REQUEST']
            );
            if ($error) return $error;

            $result = $this->verificationService->approveVerificationRequest(
                $verificationRequest,
                $user,
                $request->feedback
            );

            // Clear caches
            $this->cacheService->invalidateByTag([
                "verification_request_{$verificationRequest->id}",
                "user_{$verificationRequest->user_id}_verification",
                "admin_verification_requests"
            ]);

            return ErrorHandlingService::successResponse(
                ['verification_id' => $verificationRequest->id],
                $result ? 'Verification approved successfully' : 'Failed to approve verification',
                $result ? 200 : 400
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'approve_verification_request');
        }
    }

    /**
     * Admin: Reject verification request
     */
    public function rejectRequest(Request $request, VerificationRequest $verificationRequest): JsonResponse
    {
        try {
            $user = $request->user();

            // Check if user is admin
            $error = ErrorHandlingService::validateBusinessLogic(
                $user->is_admin,
                'Access denied',
                ErrorHandlingService::ERROR_CODES['FORBIDDEN']
            );
            if ($error) return $error;

            // Validate request data
            $validated = ValidationService::validateRequest($request, [
                'reason' => 'required|string|max:1000',
            ], [
                'reason.required' => 'Rejection reason is required',
                'reason.max' => 'Reason cannot exceed 1000 characters'
            ]);

            // Check if verification can be rejected
            $error = ErrorHandlingService::validateBusinessLogic(
                in_array($verificationRequest->status, ['pending', 'pending_review']),
                'Verification request cannot be rejected in current status',
                ErrorHandlingService::ERROR_CODES['INVALID_REQUEST']
            );
            if ($error) return $error;

            $result = $this->verificationService->rejectVerificationRequest(
                $verificationRequest,
                $user,
                $request->reason
            );

            // Clear caches
            $this->cacheService->invalidateByTag([
                "verification_request_{$verificationRequest->id}",
                "user_{$verificationRequest->user_id}_verification",
                "admin_verification_requests"
            ]);

            return ErrorHandlingService::successResponse(
                ['verification_id' => $verificationRequest->id],
                $result ? 'Verification rejected successfully' : 'Failed to reject verification',
                $result ? 200 : 400
            );
        } catch (\Exception $e) {
            return ErrorHandlingService::handleException($e, 'reject_verification_request');
        }
    }

    /**
     * Validate submission based on verification type
     */
    private function validateByType(string $verificationType, array $data): array
    {
        $rules = [];
        $messages = [];

        switch ($verificationType) {
            case 'identity':
                $rules = [
                    'submitted_data.full_name' => 'required|string|max:255',
                    'submitted_data.date_of_birth' => 'required|date|before:today',
                    'submitted_data.id_number' => 'required|string|max:50',
                    'documents.government_id' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                    'documents.selfie' => 'required|file|mimes:jpg,jpeg,png|max:5120',
                ];
                break;

            case 'employment':
                $rules = [
                    'submitted_data.company_name' => 'required|string|max:255',
                    'submitted_data.position' => 'required|string|max:255',
                    'submitted_data.employment_start_date' => 'required|date',
                    'documents.employment_letter' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ];
                break;

            case 'education':
                $rules = [
                    'submitted_data.institution_name' => 'required|string|max:255',
                    'submitted_data.degree' => 'required|string|max:255',
                    'submitted_data.graduation_year' => 'required|integer|min:1950|max:' . date('Y'),
                    'documents.diploma' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ];
                break;

            case 'income':
                $rules = [
                    'submitted_data.annual_income' => 'required|numeric|min:0',
                    'submitted_data.income_currency' => 'required|string|size:3',
                    'documents.tax_return' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                ];
                break;
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            return [
                'status' => 'error',
                'message' => 'Verification type specific validation failed',
                'errors' => $validator->errors(),
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * Get form fields for verification type
     */
    private function getFormFields(string $verificationType): array
    {
        return match ($verificationType) {
            'identity' => [
                'full_name' => ['type' => 'text', 'required' => true, 'label' => 'Full Name as on ID'],
                'date_of_birth' => ['type' => 'date', 'required' => true, 'label' => 'Date of Birth'],
                'id_number' => ['type' => 'text', 'required' => true, 'label' => 'ID Number'],
            ],
            'employment' => [
                'company_name' => ['type' => 'text', 'required' => true, 'label' => 'Company Name'],
                'position' => ['type' => 'text', 'required' => true, 'label' => 'Job Position'],
                'employment_start_date' => ['type' => 'date', 'required' => true, 'label' => 'Employment Start Date'],
            ],
            'education' => [
                'institution_name' => ['type' => 'text', 'required' => true, 'label' => 'Institution Name'],
                'degree' => ['type' => 'text', 'required' => true, 'label' => 'Degree/Qualification'],
                'graduation_year' => ['type' => 'number', 'required' => true, 'label' => 'Graduation Year'],
            ],
            'income' => [
                'annual_income' => ['type' => 'number', 'required' => true, 'label' => 'Annual Income'],
                'income_currency' => ['type' => 'select', 'required' => true, 'label' => 'Currency', 'options' => ['USD', 'UZS', 'EUR']],
            ],
            'address' => [
                'street_address' => ['type' => 'text', 'required' => true, 'label' => 'Street Address'],
                'city' => ['type' => 'text', 'required' => true, 'label' => 'City'],
                'postal_code' => ['type' => 'text', 'required' => true, 'label' => 'Postal Code'],
            ],
            'social_media' => [
                'instagram_url' => ['type' => 'url', 'required' => false, 'label' => 'Instagram Profile'],
                'facebook_url' => ['type' => 'url', 'required' => false, 'label' => 'Facebook Profile'],
                'linkedin_url' => ['type' => 'url', 'required' => false, 'label' => 'LinkedIn Profile'],
            ],
            default => []
        };
    }
}