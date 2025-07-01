# API Controller Optimization Details for YorYor Application

This document provides detailed recommendations for optimizing the API controllers in the YorYor dating application, based on analysis of the existing codebase.

## Controller Structure Improvements

### AuthController Refactoring

The current `AuthController` is over 1000 lines long and handles multiple responsibilities:
- OTP-based authentication
- Traditional login/registration
- Two-factor authentication
- Profile completion

Recommendation: Split into multiple controllers:

```php
// AuthController - Core authentication only
class AuthController extends Controller
{
    // Login, logout, and basic auth methods
}

// OtpController - OTP-specific functionality
class OtpController extends Controller
{
    // OTP generation, verification, etc.
}

// TwoFactorController - 2FA functionality
class TwoFactorController extends Controller
{
    // Enable, disable, verify 2FA
}

// RegistrationController - Registration and profile completion
class RegistrationController extends Controller
{
    // Register, complete registration
}
```

### Form Request Classes

Move validation logic from controllers to dedicated Form Request classes:

Current approach in `AuthController`:
```php
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
            'errors' => $validator->errors()
        ], 422);
    }
    
    // Method logic...
}
```

Improved approach:
```php
// App\Http\Requests\Auth\AuthenticateRequest.php
class AuthenticateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'otp' => ['nullable', 'string', 'size:4'],
        ];
    }
}

// In AuthController
public function authenticate(AuthenticateRequest $request)
{
    // Method logic with validated data...
    // $request->validated() contains the validated data
}
```

### Consistent Response Structure

Implement a consistent response structure across all controllers:

```php
// App\Http\Controllers\Api\ApiController.php
class ApiController extends Controller
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message, $errors = null, $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}

// Then extend this in all API controllers
class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        try {
            $userData = $this->authService->login($request->validated());
            return $this->successResponse($userData, 'Login successful');
        } catch (\Exception $e) {
            return $this->errorResponse('Login failed', $e->getMessage(), 500);
        }
    }
}
```

## Performance Optimizations

### N+1 Query Prevention

Current code in `MatchController::getMatches()`:
```php
$matches = $query->paginate($perPage);

// Preload mutual match status for all matches in a single query
if (!$mutualOnly) {
    $matchedUserIds = $matches->pluck('matched_user_id')->toArray();

    $mutualMatches = MatchModel::where('matched_user_id', $user->id)
        ->whereIn('user_id', $matchedUserIds)
        ->pluck('user_id')
        ->toArray();

    // Add is_mutual flag to each match without additional queries
    $matches->getCollection()->transform(function ($match) use ($mutualMatches) {
        $match->is_mutual = in_array($match->matched_user_id, $mutualMatches);
        return $match;
    });
}
```

This is a good approach to avoid N+1 queries. Similar techniques should be applied to other controllers.

### Selective Column Loading

Current code in `MatchController::getPotentialMatches()`:
```php
$potentialMatches = $query->with([
    'profile',
    'photos',
    'profilePhoto'
])->paginate($perPage);
```

Improved version with selective column loading:
```php
$potentialMatches = $query->with([
    'profile:id,user_id,first_name,last_name,age,gender,city,bio',
    'photos:id,user_id,path,order',
    'profilePhoto:id,user_id,path'
])->paginate($perPage);
```

### Consistent Caching Strategy

Current caching in `MatchController`:
```php
// In getPotentialMatches
$preference = \Cache::remember('user_preference_' . $user->id, now()->addMinutes(30), function () use ($user) {
    return $user->preference;
});

// Later in the same method
$cacheKey = 'potential_matches_' . $user->id . '_page_' . $potentialMatches->currentPage() . '_per_' . $perPage;
\Cache::put($cacheKey, $potentialMatches, now()->addMinutes(5));

// In getMatches
$cacheKey = "user_{$user->id}_matches_page_{$request->input('page', 1)}_per_{$perPage}_mutual_{$mutualOnly}";
return \Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $perPage, $mutualOnly, $request) {
    // Query logic
});
```

Improved caching with standardized keys and tags:
```php
// Create a CacheService
class CacheService
{
    const USER_PREFERENCES_TTL = 30; // minutes
    const MATCH_RESULTS_TTL = 5; // minutes
    
    public function getUserPreference($userId)
    {
        $key = "user:{$userId}:preferences";
        return \Cache::tags(['user-data', "user:{$userId}"])
            ->remember($key, now()->addMinutes(self::USER_PREFERENCES_TTL), function () use ($userId) {
                return User::find($userId)->preference;
            });
    }
    
    public function getPotentialMatches($userId, $page, $perPage, $callback)
    {
        $key = "user:{$userId}:potential-matches:page:{$page}:per:{$perPage}";
        return \Cache::tags(['matches', "user:{$userId}"])
            ->remember($key, now()->addMinutes(self::MATCH_RESULTS_TTL), $callback);
    }
    
    public function getMatches($userId, $page, $perPage, $mutualOnly, $callback)
    {
        $key = "user:{$userId}:matches:page:{$page}:per:{$perPage}:mutual:{$mutualOnly}";
        return \Cache::tags(['matches', "user:{$userId}"])
            ->remember($key, now()->addMinutes(self::MATCH_RESULTS_TTL), $callback);
    }
    
    public function invalidateUserMatches($userId)
    {
        \Cache::tags(["user:{$userId}", 'matches'])->flush();
    }
}
```

## Security Enhancements

### Authorization Implementation

Current commented-out authorization in `MatchController`:
```php
// Check if the user is authorized to view potential matches
// $this->authorize('viewAny', MatchModel::class);
```

Implement proper policies:
```php
// App\Policies\MatchPolicy.php
class MatchPolicy
{
    public function viewAny(User $user)
    {
        return $user->registration_completed && !$user->disabled_at;
    }
    
    public function create(User $user)
    {
        return $user->registration_completed && !$user->disabled_at;
    }
    
    public function delete(User $user, MatchModel $match)
    {
        return $user->id === $match->user_id;
    }
}
```

Then uncomment and use the authorization checks:
```php
public function getPotentialMatches(Request $request)
{
    $this->authorize('viewAny', MatchModel::class);
    // Method logic...
}
```

### Rate Limiting

Implement consistent rate limiting for sensitive endpoints:

```php
// In routes/api.php
Route::middleware(['auth:sanctum', 'throttle:6,1'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['throttle:3,1'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/authenticate', [AuthController::class, 'authenticate']);
});
```

## Resource Optimization

### Conditional Relationship Loading

Implement conditional loading in API resources:

```php
// App\Http\Resources\UserResource.php
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'registration_completed' => $this->registration_completed,
            'is_private' => $this->is_private,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        
        // Only include profile data if the relationship is loaded
        if ($this->relationLoaded('profile')) {
            $data['profile'] = new ProfileResource($this->profile);
        }
        
        // Only include photos if the relationship is loaded
        if ($this->relationLoaded('photos')) {
            $data['photos'] = PhotoResource::collection($this->photos);
        }
        
        return $data;
    }
}
```

## Implementation Plan

1. **Phase 1: Code Structure Improvements**
   - Create base ApiController with standardized response methods
   - Split large controllers into smaller, focused controllers
   - Implement Form Request classes for validation

2. **Phase 2: Performance Optimizations**
   - Implement selective column loading in eager loading
   - Create a CacheService for standardized caching
   - Fix N+1 query issues

3. **Phase 3: Security Enhancements**
   - Implement and enable authorization policies
   - Add rate limiting to sensitive endpoints
   - Enhance input validation

Each phase should include testing to ensure functionality is preserved and performance is improved.
