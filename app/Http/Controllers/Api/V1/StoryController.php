<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoryCollection;
use App\Http\Resources\StoryResource;
use App\Models\UserStory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Stories",
 *     description="API Endpoints for managing user stories"
 * )
 */
class StoryController extends Controller
{
    /**
     * Get stories from matched users
     *
     * @OA\Get(
     *     path="/v1/stories/matches",
     *     summary="Get stories from matched users",
     *     description="Returns stories from users that the authenticated user has matched with",
     *     operationId="getMatchedUserStories",
     *     tags={"Stories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=2),
     *                     @OA\Property(property="media_url", type="string", example="https://example.com/stories/1.jpg"),
     *                     @OA\Property(property="thumbnail_url", type="string", example="https://example.com/stories/thumbnails/1.jpg"),
     *                     @OA\Property(property="type", type="string", example="image"),
     *                     @OA\Property(property="caption", type="string", example="My story caption"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time", example="2023-01-02T00:00:00.000000Z"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="is_expired", type="boolean", example=false),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="first_name", type="string", example="Jane"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/2.jpg")
     *                     )
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
    public function getMatchedUserStories(Request $request)
    {
        try {
            $user = $request->user();

            // Get IDs of users that the current user has matched with (mutual matches)
            $matchedUserIds = $user->mutualMatches()
                ->pluck('matched_user_id')
                ->toArray();

            // Get active stories from matched users
            $stories = UserStory::whereIn('user_id', $matchedUserIds)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->with('user.profile', 'user.profilePhoto')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => StoryResource::collection($stories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get stories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new story
     *
     * @OA\Post(
     *     path="/v1/stories",
     *     summary="Create a new story",
     *     description="Creates a new story for the authenticated user",
     *     operationId="createStory",
     *     tags={"Stories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"media"},
     *                 @OA\Property(
     *                     property="media",
     *                     type="file",
     *                     description="The media file (image or video)"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     enum={"image", "video"},
     *                     default="image",
     *                     description="The type of media"
     *                 ),
     *                 @OA\Property(
     *                     property="caption",
     *                     type="string",
     *                     description="Optional caption for the story"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Story created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Story created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/StoryResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="media", type="array", @OA\Items(type="string", example="The media field is required."))
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
    public function createStory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'media' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480',
                'type' => 'required|in:image,video',
                'caption' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = $request->user();
            $mediaFile = $request->file('media');
            $type = $request->input('type', 'image');

            // Generate a unique filename
            $filename = time() . '_' . uniqid() . '.' . $mediaFile->getClientOriginalExtension();

            // Store the file
            $mediaPath = $mediaFile->storeAs('stories', $filename, 'public');
            $mediaUrl = Storage::url($mediaPath);

            // Generate thumbnail for videos or use the image itself for images
            $thumbnailUrl = null;
            if ($type === 'image') {
                $thumbnailUrl = $mediaUrl;
            } else {
                // For video, we would normally generate a thumbnail here
                // This is a simplified version
                $thumbnailUrl = $mediaUrl . '.thumbnail.jpg';
            }

            // Create the story
            $story = UserStory::create([
                'user_id' => $user->id,
                'media_url' => $mediaUrl,
                'thumbnail_url' => $thumbnailUrl,
                'type' => $type,
                'caption' => $request->input('caption'),
                'expires_at' => now()->addHours(24),
                'status' => 'active',
            ]);

            return (new StoryResource($story))
                ->additional([
                    'status' => 'success',
                    'message' => 'Story created successfully',
                ])
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create story',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a story
     *
     * @OA\Delete(
     *     path="/v1/stories/{id}",
     *     summary="Delete a story",
     *     description="Deletes a story belonging to the authenticated user",
     *     operationId="deleteStory",
     *     tags={"Stories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Story ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Story deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Story deleted successfully")
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
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete this story")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Story not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Story not found")
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
    public function deleteStory(Request $request, $id)
    {
        try {
            $user = $request->user();
            $story = UserStory::findOrFail($id);

            // Check if the user owns the story
            if ($story->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to delete this story'
                ], 403);
            }

            // Delete the media file if it exists
            if ($story->media_url) {
                $path = str_replace('/storage/', '', $story->media_url);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            // Delete the thumbnail if it exists and is different from the media
            if ($story->thumbnail_url && $story->thumbnail_url !== $story->media_url) {
                $path = str_replace('/storage/', '', $story->thumbnail_url);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            // Delete the story
            $story->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Story deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Story not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete story',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own stories
     *
     * @OA\Get(
     *     path="/v1/stories",
     *     summary="Get user's own stories",
     *     description="Returns the authenticated user's stories",
     *     operationId="getUserStories",
     *     tags={"Stories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/StoryResource")
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
    public function getUserStories(Request $request)
    {
        try {
            $user = $request->user();
            $stories = $user->stories()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => StoryResource::collection($stories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get stories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
