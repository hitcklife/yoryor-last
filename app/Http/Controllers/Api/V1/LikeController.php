<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\NewLikeEvent;
use App\Events\NewMatchEvent;
use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Dislike;
use App\Models\MatchModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Likes",
 *     description="API Endpoints for managing likes and dislikes between users"
 * )
 */
class LikeController extends Controller
{
    /**
     * Like a user
     *
     * @OA\Post(
     *     path="/v1/likes",
     *     summary="Like a user",
     *     description="Creates a like from the authenticated user to another user",
     *     operationId="likeUser",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=2, description="ID of the user to like")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Like created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User liked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="like",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="liked_user_id", type="integer", example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                 ),
     *                 @OA\Property(property="is_match", type="boolean", example=true, description="Whether this like created a match")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Cannot like yourself")
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

    public function likeUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        try {
            $user = $request->user();
            $likedUserId = $validated['user_id'];

            // Check if trying to like self
            if ($user->id === $likedUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot like yourself'
                ], 400);
            }

            // Check if the user has already liked this user
            $existingLike = Like::where('user_id', $user->id)
                ->where('liked_user_id', $likedUserId)
                ->first();

            if ($existingLike) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already liked this user'
                ], 400);
            }

            // Remove any existing dislike
            Dislike::where('user_id', $user->id)
                ->where('disliked_user_id', $likedUserId)
                ->delete();

            // Create the like
            $like = Like::create([
                'user_id' => $user->id,
                'liked_user_id' => $likedUserId,
                'liked_at' => now()
            ]);

            // Get the liked user for broadcasting
            $likedUser = User::find($likedUserId);

            // Broadcast the new like event
            broadcast(new NewLikeEvent($like, $user, $likedUser))->toOthers();

            // Check if there's a mutual like (the other user has already liked this user)
            $mutualLike = Like::where('user_id', $likedUserId)
                ->where('liked_user_id', $user->id)
                ->exists();

            $isMatch = false;
            $match = null;
            $chat = null;

            // If there's a mutual like, create a match
            if ($mutualLike) {
                // Check if a match already exists
                $existingMatch = MatchModel::where(function ($query) use ($user, $likedUserId) {
                    $query->where('user_id', $user->id)
                        ->where('matched_user_id', $likedUserId);
                })->orWhere(function ($query) use ($user, $likedUserId) {
                    $query->where('user_id', $likedUserId)
                        ->where('matched_user_id', $user->id);
                })->first();

                if (!$existingMatch) {
                    // Create the match
                    $match = MatchModel::create([
                        'user_id' => $user->id,
                        'matched_user_id' => $likedUserId,
                        'matched_at' => now()
                    ]);

                    // Broadcast the new match event
                    broadcast(new NewMatchEvent($match, $user, $likedUser))->toOthers();

                } else {
                    $match = $existingMatch;
                }
                $isMatch = true;

                // Check if a chat already exists between these users
                $existingChat = $user->getChatWith($likedUser);

                if ($existingChat === null) {
                    // Create a chat between the two users
                    $chat = \App\Models\Chat::create([
                        'type' => 'private',
                        'is_active' => true,
                        'last_activity_at' => now()
                    ]);

                    // Attach users to the chat
                    $chat->users()->attach([
                        $user->id => [
                            'joined_at' => now(),
                            'role' => 'member'
                        ],
                        $likedUserId => [
                            'joined_at' => now(),
                            'role' => 'member'
                        ]
                    ]);
                } else {
                    $chat = $existingChat;
                }

                $likedUser = User::with([
                    'profile.country',
                    'photos' => function($query) {
                        $query->where('is_private', false)
                            ->where(function($q) {
                                $q->where('status', 'approved')
                                    ->orWhere('status', 'pending');
                            })
                            ->orderBy('order');
                    },
                    'profilePhoto'
                ])->find($likedUserId);

                $userResourceData = new \App\Http\Resources\UserResource($likedUser);

                return response()->json([
                    'status' => 'success',
                    'message' => 'User liked successfully',
                    'data' => [
                        'like' => $like,
                        'is_match' => $isMatch,
                        'match' => $match,
                        'chat' => $chat,
                        'liked_user' => $userResourceData
                    ]
                ], 201);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User liked successfully',
                'data' => [
                    'like' => $like,
                    'is_match' => $isMatch
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to like user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dislike a user
     *
     * @OA\Post(
     *     path="/v1/dislikes",
     *     summary="Dislike a user",
     *     description="Creates a dislike from the authenticated user to another user",
     *     operationId="dislikeUser",
     *     tags={"Likes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=2, description="ID of the user to dislike")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dislike created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User disliked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="dislike",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="disliked_user_id", type="integer", example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Cannot dislike yourself")
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
    public function dislikeUser(Request $request)
    {
        // Check if the user is authorized to create dislikes
//        $this->authorize('create', Dislike::class);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        try {
            $user = $request->user();
            $dislikedUserId = $validated['user_id'];

            // Check if trying to dislike self
            if ($user->id === $dislikedUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot dislike yourself'
                ], 400);
            }

            // Check if the user has already disliked this user
            $existingDislike = Dislike::where('user_id', $user->id)
                ->where('disliked_user_id', $dislikedUserId)
                ->first();

            if ($existingDislike) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already disliked this user'
                ], 400);
            }

            // Remove any existing like
            Like::where('user_id', $user->id)
                ->where('liked_user_id', $dislikedUserId)
                ->delete();

            // Remove any existing match
            MatchModel::where(function ($query) use ($user, $dislikedUserId) {
                $query->where('user_id', $user->id)
                    ->where('matched_user_id', $dislikedUserId);
            })->orWhere(function ($query) use ($user, $dislikedUserId) {
                $query->where('user_id', $dislikedUserId)
                    ->where('matched_user_id', $user->id);
            })->delete();

            // Create the dislike
            $dislike = Dislike::create([
                'user_id' => $user->id,
                'disliked_user_id' => $dislikedUserId
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User disliked successfully',
                'data' => [
                    'dislike' => $dislike
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to dislike user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users who liked the authenticated user
     *
     * @OA\Get(
     *     path="/v1/likes/received",
     *     summary="Get received likes",
     *     description="Returns a list of users who liked the authenticated user",
     *     operationId="getReceivedLikes",
     *     tags={"Likes"},
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
     *                     property="likes",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=2),
     *                         @OA\Property(property="liked_user_id", type="integer", example=1),
     *                         @OA\Property(property="liked_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="first_name", type="string", example="Jane"),
     *                             @OA\Property(property="last_name", type="string", example="Doe"),
     *                             @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/2.jpg")
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
    public function getReceivedLikes(Request $request)
    {
        // Check if the user is authorized to view likes
//        $this->authorize('viewAny', Like::class);
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);

            $likes = Like::where('liked_user_id', $user->id)
                ->whereNotExists(function ($query) use ($user) {
                    $query->select('id')
                        ->from('matches')
                        ->where(function ($q) use ($user) {
                            $q->where('user_id', $user->id)
                                ->whereColumn('matched_user_id', 'likes.user_id');
                        })
                        ->orWhere(function ($q) use ($user) {
                            $q->where('matched_user_id', $user->id)
                                ->whereColumn('user_id', 'likes.user_id');
                        });
                })
                ->with(['user.profile', 'user.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo'])
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'likes' => $likes->items(),
                    'pagination' => [
                        'total' => $likes->total(),
                        'per_page' => $likes->perPage(),
                        'current_page' => $likes->currentPage(),
                        'last_page' => $likes->lastPage()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get received likes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users that the authenticated user has liked
     *
     * @OA\Get(
     *     path="/v1/likes/sent",
     *     summary="Get sent likes",
     *     description="Returns a list of users that the authenticated user has liked",
     *     operationId="getSentLikes",
     *     tags={"Likes"},
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
     *                     property="likes",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="liked_user_id", type="integer", example=2),
     *                         @OA\Property(property="liked_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(
     *                             property="liked_user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="first_name", type="string", example="Jane"),
     *                             @OA\Property(property="last_name", type="string", example="Doe"),
     *                             @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/2.jpg")
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
    public function getSentLikes(Request $request)
    {
        // Check if the user is authorized to view likes
//        $this->authorize('viewAny', Like::class);

        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);

            $likes = Like::where('user_id', $user->id)
                ->with(['likedUser.profile', 'likedUser.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo'])
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'likes' => $likes->items(),
                    'pagination' => [
                        'total' => $likes->total(),
                        'per_page' => $likes->perPage(),
                        'current_page' => $likes->currentPage(),
                        'last_page' => $likes->lastPage()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get sent likes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
