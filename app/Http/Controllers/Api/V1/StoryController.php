<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoryCollection;
use App\Http\Resources\StoryResource;
use App\Models\UserStory;
use App\Models\User;
use App\Services\MediaUploadService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Stories",
 *     description="API Endpoints for managing user stories"
 * )
 */

class StoryController extends Controller
{
    protected $mediaUploadService;
    protected $cacheService;

    public function __construct(MediaUploadService $mediaUploadService, CacheService $cacheService)
    {
        $this->mediaUploadService = $mediaUploadService;
        $this->cacheService = $cacheService;
    }

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

            return $this->cacheService->remember(
                "matched_stories:{$user->id}",
                CacheService::TTL_SHORT, // Short TTL since stories expire in 24h
                function() use ($user) {
                    // Get IDs of users that the current user has matched with (mutual matches)
                    $matchedUserIds = $user->mutualMatches()
                        ->pluck('matched_user_id')
                        ->toArray();

                    // Get active stories from matched users
                    $stories = UserStory::whereIn('user_id', $matchedUserIds)
                        ->where('status', 'active')
                        ->where('expires_at', '>', now())
                        ->with('user.profile', 'user.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    return response()->json([
                        'status' => 'success',
                        'data' => StoryResource::collection($stories)
                    ]);
                },
                ["user_{$user->id}_stories", "user_{$user->id}_matches"]
            );
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
     *     description="Creates a new story for the authenticated user with optimized media processing and AWS S3 storage",
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
     *                     description="The media file (image or video) - max 50MB for images, 100MB for videos"
     *                 ),
     *                 @OA\Property(
     *                     property="caption",
     *                     type="string",
     *                     maxLength=500,
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
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="media_url", type="string", example="https://cdn.example.com/stories/story.webp"),
     *                 @OA\Property(property="thumbnail_url", type="string", example="https://cdn.example.com/stories/story-thumb.webp"),
     *                 @OA\Property(property="type", type="string", example="image"),
     *                 @OA\Property(property="caption", type="string", example="My story caption"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="is_expired", type="boolean", example=false),
     *                 @OA\Property(property="user", type="object", nullable=true)
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
     *         response=413,
     *         description="File too large",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="File size exceeds maximum limit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
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
            // Validate the request
            $validator = $this->validateStoryRequest($request);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $mediaFile = $request->file('media');
            $caption = $request->input('caption');

            // Check if user has too many active stories (optional limit)
            $activeStoriesCount = UserStory::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->count();

            if ($activeStoriesCount >= 5) { // Max 5 active stories
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maximum active stories limit reached (5 stories)',
                    'error_code' => 'story_limit_exceeded'
                ], 422);
            }

            // Process and upload media using MediaUploadService
            $uploadResult = $this->processAndUploadMedia($mediaFile, $user->id);

            // Create the story record
            $story = $this->createStoryRecord($user->id, $uploadResult, $caption);

            // Clear story caches for all users who matched with this user
            $user->mutualMatches()->each(function ($match) {
                $this->cacheService->invalidateUserCaches($match->matched_user_id);
            });
            // Also clear cache for the story creator
            $this->cacheService->invalidateUserCaches($user->id);

            // Log successful story creation
            Log::info('Story created successfully', [
                'user_id' => $user->id,
                'story_id' => $story->id,
                'media_type' => $uploadResult['media_type'],
                'file_size' => $uploadResult['metadata']['file_size'] ?? null
            ]);

            return (new StoryResource($story))
                ->additional([
                    'status' => 'success',
                    'message' => 'Story created successfully',
                ])
                ->response()
                ->setStatusCode(201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Story creation failed', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create story',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Validate story creation request
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    private function validateStoryRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'media' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,webm',
                'max:102400' // 100MB max
            ],
            'caption' => [
                'nullable',
                'string',
                'max:500'
            ]
        ], [
            'media.required' => 'Media file is required',
            'media.file' => 'Media must be a valid file',
            'media.mimes' => 'Media must be an image (jpeg, png, jpg, gif, webp) or video (mp4, mov, avi, webm)',
            'media.max' => 'Media file size cannot exceed 100MB',
            'caption.max' => 'Caption cannot exceed 500 characters'
        ]);
    }

    /**
     * Process and upload media using MediaUploadService
     *
     * @param \Illuminate\Http\UploadedFile $mediaFile
     * @param int $userId
     * @return array
     * @throws \Exception
     */
    private function processAndUploadMedia($mediaFile, int $userId): array
    {
        try {
            // Upload and process media using MediaUploadService
            $uploadResult = $this->mediaUploadService->uploadMedia(
                $mediaFile, 
                'story', 
                $userId, 
                [
                    'context' => 'user_story',
                    'optimize' => true,
                    'generate_thumbnails' => true
                ]
            );

            // Ensure we have required URLs
            if (!isset($uploadResult['original_url'])) {
                throw new \Exception('Media upload failed - no URL returned');
            }

            return $uploadResult;

        } catch (\Exception $e) {
            Log::error('Media processing failed for story', [
                'user_id' => $userId,
                'file_name' => $mediaFile->getClientOriginalName(),
                'file_size' => $mediaFile->getSize(),
                'mime_type' => $mediaFile->getMimeType(),
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Failed to process media: ' . $e->getMessage());
        }
    }

    /**
     * Create story record in database
     *
     * @param int $userId
     * @param array $uploadResult
     * @param string|null $caption
     * @return UserStory
     */
    private function createStoryRecord(int $userId, array $uploadResult, ?string $caption): UserStory
    {
        return DB::transaction(function() use ($userId, $uploadResult, $caption) {
            return UserStory::create([
                'user_id' => $userId,
                'media_url' => $uploadResult['original_url'],
                'thumbnail_url' => $uploadResult['thumbnail_url'] ?? $uploadResult['original_url'],
                'type' => $this->determineStoryType($uploadResult['media_type']),
                'caption' => $caption,
                'expires_at' => now()->addHours(24),
                'status' => 'active',
                'metadata' => $uploadResult['metadata'] ?? []
            ]);
        });
    }

    /**
     * Determine story type from media type
     *
     * @param string $mediaType
     * @return string
     */
    private function determineStoryType(string $mediaType): string
    {
        switch ($mediaType) {
            case 'image':
                return 'image';
            case 'video':
                return 'video';
            default:
                return 'image'; // Default fallback
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

            // Delete media files from S3 using MediaUploadService
            $this->deleteStoryMediaFiles($story);

            // Delete the story record
            $story->delete();

            // Clear story caches for all users who matched with this user
            $user->mutualMatches()->each(function ($match) {
                $this->cacheService->invalidateUserCaches($match->matched_user_id);
            });
            // Also clear cache for the story creator
            $this->cacheService->invalidateUserCaches($user->id);

            // Log successful deletion
            Log::info('Story deleted successfully', [
                'user_id' => $user->id,
                'story_id' => $story->id,
                'story_type' => $story->type
            ]);

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
            Log::error('Story deletion failed', [
                'user_id' => $request->user()->id ?? null,
                'story_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete story',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete story media files from S3
     *
     * @param UserStory $story
     * @return void
     */
    private function deleteStoryMediaFiles(UserStory $story): void
    {
        try {
            $filePaths = [];

            // Collect all file paths that need to be deleted
            if ($story->media_url) {
                $filePaths[] = $this->extractS3PathFromUrl($story->media_url);
            }

            if ($story->thumbnail_url && $story->thumbnail_url !== $story->media_url) {
                $filePaths[] = $this->extractS3PathFromUrl($story->thumbnail_url);
            }

            // Filter out null paths
            $filePaths = array_filter($filePaths);

            if (!empty($filePaths)) {
                $this->mediaUploadService->deleteMediaFiles($filePaths);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to delete story media files', [
                'story_id' => $story->id,
                'media_url' => $story->media_url,
                'thumbnail_url' => $story->thumbnail_url,
                'error' => $e->getMessage()
            ]);
            // Continue with story deletion even if file deletion fails
        }
    }

    /**
     * Extract S3 path from URL
     *
     * @param string $url
     * @return string|null
     */
    private function extractS3PathFromUrl(string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Parse the URL to extract the path
        $parsedUrl = parse_url($url);
        if (!$parsedUrl || !isset($parsedUrl['path'])) {
            return null;
        }

        // Remove leading slash from path
        $path = ltrim($parsedUrl['path'], '/');

        // If using CloudFront or custom domain, the path might be the full S3 key
        // If using direct S3 URLs, we might need to remove the bucket name
        $bucketName = config('filesystems.disks.s3.bucket');
        if ($bucketName && str_starts_with($path, $bucketName . '/')) {
            $path = substr($path, strlen($bucketName) + 1);
        }

        return $path ?: null;
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
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="media_url", type="string", example="https://example.com/story.jpg"),
     *                     @OA\Property(property="thumbnail_url", type="string", example="https://example.com/story-thumb.jpg"),
     *                     @OA\Property(property="type", type="string", example="image"),
     *                     @OA\Property(property="caption", type="string", example="My story caption"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="expires_at", type="string", format="date-time"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="is_expired", type="boolean", example=false),
     *                     @OA\Property(property="user", type="object", nullable=true)
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
    public function getUserStories(Request $request)
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_stories:{$user->id}",
                CacheService::TTL_SHORT,
                function() use ($user) {
                    $stories = $user->stories()->orderBy('created_at', 'desc')->get();

                    return response()->json([
                        'status' => 'success',
                        'data' => StoryResource::collection($stories)
                    ]);
                },
                ["user_{$user->id}_stories"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get stories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
