<?php

/**
 * @OA\Schema(
 *     schema="Error",
 *     required={"status", "message"},
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="error"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Error message"
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={"field": {"Error message"}}
 *     )
 * )
 */

/**
 * @OA\Schema(
 *   schema="UserResource",
 *   type="object",
 *   @OA\Property(property="type", type="string", example="users"),
 *   @OA\Property(property="id", type="string", example="1"),
 *   @OA\Property(
 *     property="attributes",
 *     type="object",
 *     @OA\Property(property="email", type="string", example="user@example.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/1.jpg"),
 *     @OA\Property(property="registration_completed", type="boolean", example=true),
 *     @OA\Property(property="is_private", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="age", type="integer", example=25, nullable=true),
 *     @OA\Property(property="full_name", type="string", example="John Doe", nullable=true),
 *     @OA\Property(property="is_online", type="boolean", example=true),
 *     @OA\Property(property="last_active_at", type="string", format="date-time", nullable=true)
 *   ),
 *   @OA\Property(property="included", type="array", @OA\Items(type="object"), nullable=true)
 * )
 */

/**
 * @OA\Schema(
 *   schema="StoryResource",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="media_url", type="string", example="https://example.com/story.jpg"),
 *   @OA\Property(property="thumbnail_url", type="string", example="https://example.com/story-thumb.jpg"),
 *   @OA\Property(property="type", type="string", example="image"),
 *   @OA\Property(property="caption", type="string", example="My story caption"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="expires_at", type="string", format="date-time"),
 *   @OA\Property(property="status", type="string", example="active"),
 *   @OA\Property(property="is_expired", type="boolean", example=false),
 *   @OA\Property(property="user", ref="#/components/schemas/UserResource", nullable=true)
 * )
 */

namespace App\Http\Controllers\Api\V1;

/**
 * @OA\Info(
 *     title="YorYor API",
 *     version="1.0.0",
 *     description="API documentation for YorYor dating application",
 *     @OA\Contact(
 *         email="support@yoryor.com",
 *         name="YorYor Support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 */

/**
 * @OA\Server(
 *     url="/api/",
 *     description="API Server"
 * )
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Rules\StrongPassword;
use App\Services\AuthService;
use App\Services\OtpService;
use App\Services\TwoFactorAuthService;
use App\Services\MediaUploadService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;
    protected $otpService;
    protected $twoFactorAuthService;
    protected $mediaUploadService;
    protected $cacheService;

    public function __construct(
        AuthService $authService,
        OtpService $otpService,
        TwoFactorAuthService $twoFactorAuthService,
        MediaUploadService $mediaUploadService,
        CacheService $cacheService
    )
    {
        $this->authService = $authService;
        $this->otpService = $otpService;
        $this->twoFactorAuthService = $twoFactorAuthService;
        $this->mediaUploadService = $mediaUploadService;
        $this->cacheService = $cacheService;
    }

    /**
     * Authenticate user with OTP
     *
     * @OA\Post(
     *     path="/v1/auth/authenticate",
     *     summary="Authenticate user with OTP",
     *     description="Sends OTP to phone number, verifies it if provided, and returns user data based on registration status",
     *     operationId="authenticateWithOtp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="User's phone number"),
     *             @OA\Property(property="otp", type="string", example="123456", description="One-time password (if verifying)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully or Authentication successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="otp_sent", type="boolean", example=true, description="Indicates if OTP was sent"),
     *                 @OA\Property(property="authenticated", type="boolean", example=false, description="Indicates if user is authenticated"),
     *                 @OA\Property(property="registration_completed", type="boolean", example=false, description="Indicates if registration is completed"),
     *                 @OA\Property(property="expires_in", type="integer", example=300, description="OTP expiry time in seconds"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="registration_completed", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(
     *                         property="profile",
     *                         type="object",
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe")
     *                     ),
     *                     @OA\Property(property="photos", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="likes", type="array", @OA\Items(type="object"))
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|laravel_sanctum_token...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or invalid OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"field": {"Error message"}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Authentication failed"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:20'],
            'otp' => ['nullable', 'string', 'size:4'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $phone = $request->input('phone');
            $otp = $request->input('otp');

            // If OTP is not provided, generate and send one
            if (!$otp) {
                $otpData = $this->otpService->generateOtp($phone);

                // Send OTP via SMS
                $this->otpService->sendOtpSms($phone, $otpData['otp']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP sent successfully',
                    'data' => [
                        'otp_sent' => true,
                        'authenticated' => false,
                        'registration_completed' => false,
                        'phone' => $phone,
                        'expires_in' => $otpData['expires_in']
                    ]
                ]);
            }

            // If OTP is provided, verify it
            $userData = $this->otpService->verifyOtp($phone, $otp);
            $user = $userData['user'];
            $isRegistrationCompleted = $user->registration_completed;

            // Update last login timestamp
            $user->last_login_at = now();
            $user->save();

            // Prepare response data
            $responseData = [
                'otp_sent' => false,
                'authenticated' => true,
                'registration_completed' => $isRegistrationCompleted,
                'user' => $user,
                'token' => $userData['token']
            ];

            // If registration is completed, include additional user data
            if ($isRegistrationCompleted) {
                $user->load(['profile', 'photos']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Authentication successful',
                'data' => $responseData
            ]);
        } catch (ValidationException $e) {
            // Check if it's an OTP validation error (invalid credentials)
            $errors = $e->errors();
            if (isset($errors['otp']) && $otp) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed',
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new user
     *
     * @OA\Post(
     *     path="/v1/auth/register",
     *     summary="Register a new user",
     *     description="Creates a new user account with profile and preferences",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "first_name", "last_name", "date_of_birth", "gender"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="User's email address (required if phone not provided)"),
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="User's phone number (required if email not provided)"),
     *             @OA\Property(property="password", type="string", format="password", example="SecureP@ss123", description="User's password (min 8 chars, mixed case, numbers, symbols)"),
     *             @OA\Property(property="first_name", type="string", example="John", description="User's first name"),
     *             @OA\Property(property="last_name", type="string", example="Doe", description="User's last name"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01", description="User's date of birth (must be 18+ years old)"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "non-binary", "other"}, example="male", description="User's gender")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|laravel_sanctum_token...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"field": {"Error message"}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Registration failed"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required_without:phone', 'email', 'unique:users,email', 'max:255', 'nullable'],
            'phone' => ['required_without:email', 'unique:users,phone', 'max:20', 'nullable'],
            'password' => ['required', new StrongPassword()],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:-18 years'],
            'gender' => ['required', 'in:male,female,non-binary,other'],
            'country' => ['nullable', 'string'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $this->authService->register($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => $userData
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     *
     * @OA\Post(
     *     path="/v1/auth/login",
     *     summary="Login user",
     *     description="Authenticates a user and returns a token",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="User's email address (required if phone not provided)"),
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="User's phone number (required if email not provided)"),
     *             @OA\Property(property="password", type="string", format="password", example="SecureP@ss123", description="User's password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|laravel_sanctum_token...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Login failed"),
     *             @OA\Property(property="errors", type="object", example={"field": {"Error message"}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Account is disabled",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Account is disabled")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Login failed"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required_without:phone', 'email', 'nullable'],
            'phone' => ['required_without:email', 'nullable'],
            'password' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $this->authService->login($request->only(['email', 'phone', 'password']));

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $userData
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     *
     * @OA\Post(
     *     path="/v1/auth/logout",
     *     summary="Logout user",
     *     description="Invalidates the user's authentication token",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Logout failed"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request OTP for phone login
     *
     * @OA\Post(
     *     path="/v1/auth/request-otp",
     *     summary="Request OTP for phone login",
     *     description="Sends a one-time password to the provided phone number",
     *     operationId="requestOtp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone"},
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="User's phone number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP sent successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="phone", type="string", example="+1234567890"),
     *                 @OA\Property(property="expires_in", type="integer", example=300, description="OTP expiry time in seconds")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"field": {"Error message"}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to send OTP"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function requestOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        try {
            $otpData = $this->otpService->generateOtp($validated['phone']);

            // In a real application, you would send the OTP via SMS here
            $this->otpService->sendOtpSms($validated['phone'], $otpData['otp']);

            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully',
                'data' => [
                    'phone' => $validated['phone'],
                    'expires_in' => $otpData['expires_in']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP and login/register user
     *
     * @OA\Post(
     *     path="/v1/auth/verify-otp",
     *     summary="Verify OTP and login/register user",
     *     description="Verifies the OTP sent to the phone number and logs in or registers the user",
     *     operationId="verifyOtp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "otp"},
     *             @OA\Property(property="phone", type="string", example="+1234567890", description="User's phone number"),
     *             @OA\Property(property="otp", type="string", example="123456", description="One-time password sent to the phone")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP verified successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="phone_verified_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="registration_completed", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|laravel_sanctum_token..."),
     *                 @OA\Property(property="is_new_user", type="boolean", example=true, description="Indicates if the user was just created")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or invalid OTP",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="OTP verification failed"),
     *             @OA\Property(property="errors", type="object", example={"field": {"Error message"}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="OTP verification failed"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        try {
            $userData = $this->otpService->verifyOtp($validated['phone'], $validated['otp']);

            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'data' => $userData
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP verification failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete registration for OTP users
     *
     * @OA\Post(
     *     path="/v1/auth/complete-registration",
     *     summary="Complete registration for OTP users",
     *     description="Completes the registration process for users who signed up via OTP",
     *     operationId="completeRegistration",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"firstName", "lastName", "dateOfBirth", "gender"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="User's email address (optional)"),
     *             @OA\Property(property="firstName", type="string", example="John", description="User's first name"),
     *             @OA\Property(property="lastName", type="string", example="Doe", description="User's last name"),
     *             @OA\Property(property="dateOfBirth", type="string", format="date", example="1990-01-01", description="User's date of birth (must be 18+ years old)"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "non-binary", "other"}, example="male", description="User's gender"),
     *             @OA\Property(property="age", type="integer", example=31, description="User's age (calculated from date of birth)"),
     *             @OA\Property(property="status", type="string", example="single", description="User's relationship status"),
     *             @OA\Property(property="occupation", type="string", example="employee", description="User's occupation"),
     *             @OA\Property(property="profession", type="string", example="Engineer", description="User's profession"),
     *             @OA\Property(property="bio", type="string", example="I'm an Engineer from NY and I love reading books", description="User's biography"),
     *             @OA\Property(property="interests", type="array", @OA\Items(type="string"), example={"music", "gaming", "dancing"}, description="User's interests"),
     *             @OA\Property(property="country", type="string", example="United States", description="User's country"),
     *             @OA\Property(property="countryCode", type="string", example="US", description="User's country code"),
     *             @OA\Property(property="state", type="string", example="New York", description="User's state"),
     *             @OA\Property(property="city", type="string", example="Westbury", description="User's city")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Registration completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="registration_completed", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or registration already completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function completeRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['nullable', 'email', 'unique:users,email', 'max:255'],
            'firstName' => ['required', 'string', 'max:50'],
            'lastName' => ['required', 'string', 'max:50'],
            'dateOfBirth' => ['required', 'date', 'before:-18 years'],
            'gender' => ['required', 'in:male,female,non-binary,other'],
            'age' => ['nullable', 'integer', 'min:18', 'max:120'],
            'status' => ['nullable', 'string', 'max:50'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'profession' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'country' => ['nullable', 'string', 'max:100'],
            'countryCode' => ['nullable', 'string', 'max:10'],
            'state' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:85'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['nullable', 'required_without:photos.*.name', 'file', 'image', 'max:10240'], // 10MB max for web uploads
            'photos.*.name' => ['nullable', 'required_without:photos.*', 'string'], // For mobile app
            'photos.*.size' => ['nullable', 'string'], // For mobile app
            'mainPhotoIndex' => ['nullable', 'string'],
            'profile_private' => ['nullable'], // Accept any value and convert to boolean
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error_code' => 'VALIDATION_ERROR',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $this->authService->completeRegistration($request->user(), $request->all());

            // Load photos relationship
            $user->load('photos');

            return response()->json([
                'status' => 'success',
                'message' => 'Registration completed successfully',
                'data' => [
                    'user' => $user
                ]
            ]);
        } catch (\Exception $e) {
            // Check if it's a registration already completed error
            if ($e->getMessage() === 'Registration already completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration already completed',
                    'error_code' => 'FORBIDDEN'
                ], 403);
            }
            
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enable two-factor authentication
     *
     * @OA\Post(
     *     path="/v1/auth/2fa/enable",
     *     summary="Enable two-factor authentication",
     *     description="Enables two-factor authentication for the authenticated user",
     *     operationId="enableTwoFactor",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Two-factor authentication enabled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Two-factor authentication enabled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="secret_key", type="string", example="ABCDEFGHIJKLMNOP"),
     *                 @OA\Property(property="qr_code_url", type="string", example="otpauth://totp/YorYor:user@example.com?secret=ABCDEFGHIJKLMNOP&issuer=YorYor"),
     *                 @OA\Property(property="recovery_codes", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enableTwoFactor(Request $request)
    {
        try {
            $user = $request->user();
            $twoFactorData = $this->twoFactorAuthService->enableTwoFactor($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Two-factor authentication enabled successfully',
                'data' => $twoFactorData
            ]);
        } catch (\Exception $e) {
            // Check if 2FA is already enabled
            if ($e->getMessage() === 'Two-factor authentication is already enabled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Two-factor authentication is already enabled',
                    'error_code' => 'ALREADY_ENABLED'
                ], 409);
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to enable two-factor authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disable two-factor authentication
     *
     * @OA\Post(
     *     path="/v1/auth/2fa/disable",
     *     summary="Disable two-factor authentication",
     *     description="Disables two-factor authentication for the authenticated user",
     *     operationId="disableTwoFactor",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Two-factor authentication disabled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Two-factor authentication disabled successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function disableTwoFactor(Request $request)
    {
        try {
            $user = $request->user();
            $this->twoFactorAuthService->disableTwoFactor($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Two-factor authentication disabled'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to disable two-factor authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify two-factor authentication code
     *
     * @OA\Post(
     *     path="/v1/auth/2fa/verify",
     *     summary="Verify two-factor authentication code",
     *     description="Verifies a two-factor authentication code for the authenticated user",
     *     operationId="verifyTwoFactorCode",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="123456", description="Two-factor authentication code")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Two-factor authentication code verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Two-factor authentication code verified successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or invalid code",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyTwoFactorCode(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $user = $request->user();
            $verified = $this->twoFactorAuthService->verifyCode($user, $validated['code']);

            if (!$verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid two-factor authentication code',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Two-factor authentication code verified successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify two-factor authentication code',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Check if an email is already taken
     *
     * @OA\Post(
     *     path="/v1/auth/check-email",
     *     summary="Check if an email is already taken",
     *     description="Checks if the provided email address is already registered in the system",
     *     operationId="checkEmail",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Email address to check")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email availability check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_taken", type="boolean", example="false"),
     *                 @OA\Property(property="email", type="string", example="user@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function checkEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'error_code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = strtolower(trim($request->input('email')));

            // Cache email checks for 5 minutes to reduce database load
            $cacheKey = "email_check:" . md5($email);
            $isTaken = $this->cacheService->remember($cacheKey, 300, function () use ($email) {
                return User::where('email', $email)->exists();
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'is_taken' => $isTaken,
                    'available' => !$isTaken,
                    'email' => $email
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Email check failed', [
                'email' => $request->input('email'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check email availability',
                'error_code' => 'EMAIL_CHECK_FAILED'
            ], 500);
        }
    }
}
