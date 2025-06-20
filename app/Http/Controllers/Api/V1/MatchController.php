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

/**
 * @OA\Tag(
 *     name="Matches",
 *     description="API Endpoints for managing matches between users"
 * )
 */
class MatchController extends Controller
{
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
        // Check if the user is authorized to view potential matches
        $this->authorize('viewAny', MatchModel::class);

        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);

            // Get user's preference with caching
            $preference = \Cache::remember('user_preference_' . $user->id, now()->addMinutes(30), function () use ($user) {
                return $user->preference;
            });

            // Get users who match the preference criteria
            // Filter out users who are private unless they've matched with the current user
            $query = User::where('id', '!=', $user->id)
                         ->where('is_private', false)
                         ->where('registration_completed', true)
                         ->whereNull('disabled_at');

            // Apply preference filters if they exist
            if ($preference) {
                $query->whereHas('profile', function ($subQuery) use ($preference) {
                    if ($preference->gender) {
                        $subQuery->where('gender', $preference->gender);
                    }

                    if ($preference->min_age && $preference->max_age) {
                        // Use the age field directly if available
                        $subQuery->whereBetween('age', [$preference->min_age, $preference->max_age]);

                        // Fallback to date_of_birth calculation if age is null
                        $minBirthDate = now()->subYears($preference->max_age)->format('Y-m-d');
                        $maxBirthDate = now()->subYears($preference->min_age)->format('Y-m-d');
                        $subQuery->orWhere(function($q) use ($minBirthDate, $maxBirthDate) {
                            $q->whereNull('age')
                              ->whereBetween('date_of_birth', [$minBirthDate, $maxBirthDate]);
                        });
                    }

                    if ($preference->country) {
                        // If country is a numeric ID, use it directly
                        if (is_numeric($preference->country)) {
                            $subQuery->where('country_id', $preference->country);
                        } else {
                            // Otherwise, join with countries table to find matching countries
                            $subQuery->whereHas('country', function($countryQuery) use ($preference) {
                                $countryQuery->where('name', 'like', '%' . $preference->country . '%')
                                           ->orWhere('code', $preference->country);
                            });
                        }
                    }
                });
            }

            // Use a single query with joins instead of multiple whereNotIn subqueries
            $query->whereNotExists(function ($subQuery) use ($user) {
                $subQuery->select(\DB::raw(1))
                    ->from('likes')
                    ->whereColumn('likes.liked_user_id', 'users.id')
                    ->where('likes.user_id', $user->id);
            })
            ->whereNotExists(function ($subQuery) use ($user) {
                $subQuery->select(\DB::raw(1))
                    ->from('dislikes')
                    ->whereColumn('dislikes.disliked_user_id', 'users.id')
                    ->where('dislikes.user_id', $user->id);
            })
            ->whereNotExists(function ($subQuery) use ($user) {
                $subQuery->select(\DB::raw(1))
                    ->from('matches')
                    ->whereColumn('matches.matched_user_id', 'users.id')
                    ->where('matches.user_id', $user->id);
            });

            // Eager load relationships to reduce N+1 query problems
            $potentialMatches = $query->with([
                'profile',
                'photos',
                'profilePhoto'
            ])->paginate($perPage);

            // Cache the results for a short period
            $cacheKey = 'potential_matches_' . $user->id . '_page_' . $potentialMatches->currentPage() . '_per_' . $perPage;
            \Cache::put($cacheKey, $potentialMatches, now()->addMinutes(5));

            return (new UserCollection($potentialMatches))
                ->additional(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get potential matches',
                'error' => $e->getMessage()
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
//        // Check if the user is authorized to view matches
//        $this->authorize('viewAny', MatchModel::class);

        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);
            $mutualOnly = $request->boolean('mutual', false);

            // Generate cache key based on request parameters
            $cacheKey = "user_{$user->id}_matches_page_{$request->input('page', 1)}_per_{$perPage}_mutual_{$mutualOnly}";

            // Try to get from cache first
            return \Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $perPage, $mutualOnly, $request) {
                // Build the query with eager loading
                $query = MatchModel::where('user_id', $user->id)
                    ->with([
                        'matchedUser.profile',
                        'matchedUser.profilePhoto',
                        'matchedUser.photos'
                    ]);

                if ($mutualOnly) {
                    // More efficient query for mutual matches using a join instead of a subquery
                    $query->join('matches as mutual_matches', function ($join) use ($user) {
                        $join->on('matches.matched_user_id', '=', 'mutual_matches.user_id')
                            ->where('mutual_matches.matched_user_id', '=', $user->id);
                    })
                    ->select('matches.*');
                }

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
                } else {
                    // If we're only showing mutual matches, they're all mutual by definition
                    $matches->getCollection()->transform(function ($match) {
                        $match->is_mutual = true;
                        return $match;
                    });
                }

                return new MatchCollection($matches);
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
            $this->authorize('delete', $match);

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
