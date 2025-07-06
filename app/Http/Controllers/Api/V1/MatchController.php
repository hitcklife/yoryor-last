<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MatchCollection;
use App\Http\Resources\MatchResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\MatchModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


/**
 * @OA\Tag(
 *     name="Matches",
 *     description="API Endpoints for managing matches between users"
 * )
 */
class MatchController extends Controller
{
    use AuthorizesRequests;
    /**
     * Get potential matches for the authenticated user
     *
     * @OA\Get(
     *     path="/v1/matches/potential",
     *     summary="Get potential matches",
     *     description="Returns a list of potential matches for the authenticated user based on preferences",
     *     operationId="getPotentialMatches",
     *     tags={"Matches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="age", type="integer", example=25),
     *                         @OA\Property(property="gender", type="string", example="male"),
     *                         @OA\Property(property="bio", type="string", example="I love hiking and reading"),
     *                         @OA\Property(property="location", type="string", example="New York, NY"),
     *                         @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/1.jpg"),
     *                         @OA\Property(
     *                             property="photos",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="url", type="string", example="https://example.com/photos/1.jpg"),
     *                                 @OA\Property(property="order", type="integer", example=1)
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=10)
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
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function getPotentialMatches(Request $request)
    {
    try {
        $user = $request->user();
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Use cache to improve performance
        $cacheKey = "potential_matches_{$user->id}_page_{$page}_per_{$perPage}";

        return \Cache::remember($cacheKey, now()->addMinutes(5), function() use ($user, $perPage, $page) {
            // Start with base query using joins for better performance
            $query = User::select('users.*')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                ->where('users.id', '!=', $user->id)
                ->where('users.registration_completed', true)
                ->whereNull('users.disabled_at');

            // For now, let's get user preferences but not apply filters
            $preference = $user->preference;

            // Exclude users already interacted with - single optimized query
            $query->whereNotExists(function ($subQuery) use ($user) {
                $subQuery->selectRaw('1')
                    ->from('likes')
                    ->whereColumn('likes.liked_user_id', 'users.id')
                    ->where('likes.user_id', $user->id);
            })
            ->whereNotExists(function ($subQuery) use ($user) {
                $subQuery->selectRaw('1')
                    ->from('dislikes')
                    ->whereColumn('dislikes.disliked_user_id', 'users.id')
                    ->where('dislikes.user_id', $user->id);
            })
            ->whereNotExists(function ($subQuery) use ($user) {
                $subQuery->selectRaw('1')
                    ->from('matches')
                    ->whereColumn('matches.matched_user_id', 'users.id')
                    ->where('matches.user_id', $user->id);
            });

            // Add ordering for better user experience
            $query->orderBy('users.last_active_at', 'desc');

            // Optimized eager loading - only load what we need
            $potentialMatches = $query->with([
                'profile:id,user_id,first_name,last_name,gender,date_of_birth,city,state,province,country_id,latitude,longitude,bio,profession,interests,status,looking_for',
                'photos' => function($query) {
                    $query->where('is_private', false)
                          ->where(function($q) {
                              $q->where('status', 'approved')
                                ->orWhere('status', 'pending');
                          })
                          ->orderBy('order')
                          ->select('id', 'user_id', 'original_url', 'thumbnail_url', 'medium_url', 'is_profile_photo', 'order', 'status', 'uploaded_at');
                },
                'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo,order,status,uploaded_at'
            ])->paginate($perPage);

            // Log performance metrics
            \Log::info('Potential matches performance:', [
                'total_users' => $potentialMatches->total(),
                'query_time' => round(microtime(true) - LARAVEL_START, 3) . 's',
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . 'MB',
            ]);

            return (new UserCollection($potentialMatches))
                ->additional(['status' => 'success']);
        });

    } catch (\Exception $e) {
        \Log::error('Error in getPotentialMatches: ' . $e->getMessage(), [
            'user_id' => $request->user()?->id,
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to get potential matches',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

    /**
     * Create a match with another user
     *
     * @OA\Post(
     *     path="/v1/matches",
     *     summary="Create a match",
     *     description="Creates a match between the authenticated user and another user",
     *     operationId="createMatch",
     *     tags={"Matches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=2, description="ID of the user to match with")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Match created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Match created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="match",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="matched_user_id", type="integer", example=2),
     *                     @OA\Property(property="matched_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                 ),
     *                 @OA\Property(property="is_mutual", type="boolean", example=true, description="Whether the match is mutual")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Cannot match with yourself")
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
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function createMatch(Request $request)
    {
        // Check if the user is authorized to create matches
//        $this->authorize('create', MatchModel::class);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        try {
            $user = $request->user();
            $matchedUserId = $validated['user_id'];

            // Check if trying to match with self
            if ($user->id === $matchedUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot match with yourself'
                ], 400);
            }

            // Use a transaction to ensure data consistency
            return \DB::transaction(function () use ($user, $matchedUserId) {
                // Check if the match already exists - within transaction to prevent race conditions
                $existingMatch = MatchModel::where('user_id', $user->id)
                    ->where('matched_user_id', $matchedUserId)
                    ->lockForUpdate()
                    ->first();

                if ($existingMatch) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Match already exists'
                    ], 400);
                }

                // Create the match
                $match = MatchModel::create([
                    'user_id' => $user->id,
                    'matched_user_id' => $matchedUserId,
                    'matched_at' => now()
                ]);

                // Check if there's a mutual match
                $mutualMatch = MatchModel::where('user_id', $matchedUserId)
                    ->where('matched_user_id', $user->id)
                    ->exists();

                // If there's a mutual match, create a chat
                $chat = null;
                if ($mutualMatch) {
                    // Create a chat between the two users
                    $chat = \App\Models\Chat::create([
                        'user_id_1' => $user->id,
                        'user_id_2' => $matchedUserId
                    ]);

                    // Attach users to the chat
                    $chat->users()->attach([$user->id, $matchedUserId]);
                }

                // Clear any cached data that might be affected by this change
                \Cache::forget('user_' . $user->id . '_matches_page_1_per_10_mutual_0');
                \Cache::forget('user_' . $matchedUserId . '_matches_page_1_per_10_mutual_0');
                \Cache::forget('potential_matches_' . $user->id . '_page_1_per_10');
                \Cache::forget('potential_matches_' . $matchedUserId . '_page_1_per_10');

                // Set additional properties for the resource
                $match->is_mutual = $mutualMatch;
                if ($mutualMatch && $chat) {
                    $match->chat = $chat;
                }

                return (new MatchResource($match))
                    ->additional([
                        'status' => 'success',
                        'message' => 'Match created successfully',
                    ])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all matches for the authenticated user
     *
     * @OA\Get(
     *     path="/v1/matches",
     *     summary="Get user matches",
     *     description="Returns all matches for the authenticated user",
     *     operationId="getMatches",
     *     tags={"Matches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="mutual",
     *         in="query",
     *         description="Filter by mutual matches only",
     *         required=false,
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="matches",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="matched_user_id", type="integer", example=2),
     *                         @OA\Property(property="matched_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(
     *                             property="matched_user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="first_name", type="string", example="Jane"),
     *                             @OA\Property(property="last_name", type="string", example="Doe"),
     *                             @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/2.jpg")
     *                         ),
     *                         @OA\Property(property="is_mutual", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=10)
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
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */


    public function getMatches(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);

            // Generate cache key based on request parameters
            $cacheKey = "user_{$user->id}_matches_page_{$request->input('page', 1)}_per_{$perPage}";

            // Try to get from cache first
            return \Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $perPage) {
                // Get matches where user is either user_id or matched_user_id
                $matches = MatchModel::where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('matched_user_id', $user->id);
                })
                    ->with([
                        'user.profile',
                        'user.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo',
                        'user.photos',
                        'matchedUser.profile',
                        'matchedUser.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo',
                        'matchedUser.photos'
                    ])
                    ->paginate($perPage);

                // Transform matches to always show the other user
                $matches->getCollection()->transform(function ($match) use ($user) {
                    // Set the other user as the matched user for consistency
                    if ($match->user_id === $user->id) {
                        // Current structure is correct
                        $match->is_mutual = true;
                    } else {
                        // Swap the users so matched_user is always the other user
                        $tempUser = $match->user;
                        $match->user = $match->matchedUser;
                        $match->matchedUser = $tempUser;
                        $match->is_mutual = true;
                    }
                    return $match;
                });

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'matches' => $matches->items(),
                        'pagination' => [
                            'total' => $matches->total(),
                            'per_page' => $matches->perPage(),
                            'current_page' => $matches->currentPage(),
                            'last_page' => $matches->lastPage()
                        ]
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Delete a match
     *
     * @OA\Delete(
     *     path="/v1/matches/{id}",
     *     summary="Delete a match",
     *     description="Deletes a match between the authenticated user and another user",
     *     operationId="deleteMatch",
     *     tags={"Matches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Match ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Match deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Match deleted successfully")
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
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete this match")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Match not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function deleteMatch(Request $request, $id)
    {
        try {
            $user = $request->user();
            $match = MatchModel::findOrFail($id);

            // Check if the user is authorized to delete this match
//            $this->authorize('delete', $match);

            $match->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Match deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Match not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete match',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
