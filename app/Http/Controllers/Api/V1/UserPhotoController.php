<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPhoto;
use App\Services\ImageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class UserPhotoController extends Controller
{
    protected $imageProcessingService;

    public function __construct(ImageProcessingService $imageProcessingService)
    {
        $this->imageProcessingService = $imageProcessingService;
    }

    /**
     * Upload a new photo for the authenticated user
     *
     * @OA\Post(
     *     path="/v1/photos/upload",
     *     summary="Upload user photo",
     *     description="Upload a new photo for the authenticated user",
     *     operationId="uploadPhoto",
     *     tags={"Photos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Photo file to upload"
     *                 ),
     *                 @OA\Property(
     *                     property="is_profile_photo",
     *                     type="boolean",
     *                     default=false,
     *                     description="Set as profile photo"
     *                 ),
     *                 @OA\Property(
     *                     property="order",
     *                     type="integer",
     *                     default=0,
     *                     description="Display order of the photo"
     *                 ),
     *                 @OA\Property(
     *                     property="is_private",
     *                     type="boolean",
     *                     default=false,
     *                     description="Set photo as private"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Photo uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="original_url", type="string", example="https://example.com/storage/photos/user_1_original.jpg"),
     *                 @OA\Property(property="medium_url", type="string", example="https://example.com/storage/photos/user_1_medium.jpg"),
     *                 @OA\Property(property="thumbnail_url", type="string", example="https://example.com/storage/photos/user_1_thumbnail.jpg"),
     *                 @OA\Property(property="is_profile_photo", type="boolean", example=true),
     *                 @OA\Property(property="order", type="integer", example=0),
     *                 @OA\Property(property="is_private", type="boolean", example=false),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
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
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB max
            'is_profile_photo' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0', 'max:5'],
            'is_private' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $uploadedFile = $request->file('photo');

            // Additional validation using the service
            if (!$this->imageProcessingService->validateImage($uploadedFile)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid image file',
                    'error_code' => 'invalid_image'
                ], 422);
            }

            // Check photo limit (max 6 photos per user)
            $currentPhotoCount = UserPhoto::where('user_id', $user->id)->count();
            if ($currentPhotoCount >= 6) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maximum photo limit reached (6 photos)',
                    'error_code' => 'photo_limit_exceeded'
                ], 422);
            }

            // Process the image
            $processedImages = $this->imageProcessingService->processImage($uploadedFile, $user->id);

            // Handle profile photo logic
            $isProfilePhoto = $request->input('is_profile_photo', false);
            
            DB::transaction(function() use ($user, $processedImages, $isProfilePhoto, $request, &$photo) {
                if ($isProfilePhoto) {
                    // If this is set as profile photo, unset any existing profile photos
                    UserPhoto::where('user_id', $user->id)
                        ->where('is_profile_photo', true)
                        ->update(['is_profile_photo' => false]);
                } else {
                    // If no profile photo exists, make this one the profile photo
                    $hasProfilePhoto = UserPhoto::where('user_id', $user->id)
                        ->where('is_profile_photo', true)
                        ->exists();

                    if (!$hasProfilePhoto) {
                        $isProfilePhoto = true;
                    }
                }

                // Create the photo record
                $photo = UserPhoto::create([
                    'user_id' => $user->id,
                    'original_url' => $processedImages['original_url'],
                    'medium_url' => $processedImages['medium_url'],
                    'thumbnail_url' => $processedImages['thumbnail_url'],
                    'is_profile_photo' => $isProfilePhoto,
                    'order' => $request->input('order', 0),
                    'is_private' => $request->input('is_private', false),
                    'status' => 'approved', // Auto-approve for now
                    'is_verified' => false,
                    'metadata' => $processedImages['metadata'],
                    'uploaded_at' => now(),
                ]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Photo uploaded successfully',
                'data' => [
                    'id' => $photo->id,
                    'user_id' => $photo->user_id,
                    'original_url' => $photo->original_url,
                    'medium_url' => $photo->medium_url,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'is_profile_photo' => $photo->is_profile_photo,
                    'order' => $photo->order,
                    'is_private' => $photo->is_private,
                    'status' => $photo->status,
                    'created_at' => $photo->created_at,
                    'updated_at' => $photo->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all photos for the authenticated user
     *
     * @OA\Get(
     *     path="/v1/photos",
     *     summary="Get user photos",
     *     description="Get all photos for the authenticated user with optional size parameter",
     *     operationId="getUserPhotos",
     *     tags={"Photos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         description="Image size preference (thumbnail, medium, original)",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"thumbnail", "medium", "original"},
     *             default="medium"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photos retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="image_url", type="string", example="https://example.com/storage/photos/user_1_medium.jpg"),
     *                     @OA\Property(property="original_url", type="string", example="https://example.com/storage/photos/user_1_original.jpg"),
     *                     @OA\Property(property="medium_url", type="string", example="https://example.com/storage/photos/user_1_medium.jpg"),
     *                     @OA\Property(property="thumbnail_url", type="string", example="https://example.com/storage/photos/user_1_thumbnail.jpg"),
     *                     @OA\Property(property="is_profile_photo", type="boolean", example=true),
     *                     @OA\Property(property="order", type="integer", example=0),
     *                     @OA\Property(property="is_private", type="boolean", example=false),
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
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $size = $request->query('size', 'medium');
            
            $photos = UserPhoto::where('user_id', $user->id)
                ->approved()
                ->ordered()
                ->get();

            // Transform the photos to include the appropriate image URL
            $transformedPhotos = $photos->map(function ($photo) use ($size) {
                $data = $photo->toArray();
                $data['image_url'] = $this->imageProcessingService->getImageUrl(
                    $photo->original_url,
                    $photo->medium_url,
                    $photo->thumbnail_url,
                    $size
                );
                return $data;
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedPhotos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve photos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a photo
     *
     * @OA\Delete(
     *     path="/v1/photos/{id}",
     *     summary="Delete user photo",
     *     description="Delete a photo for the authenticated user",
     *     operationId="deletePhoto",
     *     tags={"Photos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Photo ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Photo deleted successfully")
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
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete this photo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Photo not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Photo not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $photo = UserPhoto::findOrFail($id);

            // Check if the photo belongs to the authenticated user
            if ($photo->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to delete this photo'
                ], 403);
            }

            DB::transaction(function() use ($photo, $user) {
                // If this is a profile photo and there are other photos, make another one the profile photo
                if ($photo->is_profile_photo) {
                    $nextPhoto = UserPhoto::where('user_id', $user->id)
                        ->where('id', '!=', $photo->id)
                        ->approved()
                        ->orderBy('order', 'asc')
                        ->first();

                    if ($nextPhoto) {
                        $nextPhoto->update(['is_profile_photo' => true]);
                    }
                }

                // Delete the files from storage
                $filePaths = $this->imageProcessingService->extractFilePaths(
                    $photo->original_url,
                    $photo->medium_url,
                    $photo->thumbnail_url
                );
                
                $this->imageProcessingService->deleteImageFiles($filePaths);

                // Delete the photo record
                $photo->delete();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Photo deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Photo not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update photo order and settings
     *
     * @OA\Put(
     *     path="/v1/photos/{id}",
     *     summary="Update photo settings",
     *     description="Update photo order, privacy, and profile photo status",
     *     operationId="updatePhoto",
     *     tags={"Photos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Photo ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="is_profile_photo", type="boolean", description="Set as profile photo"),
     *             @OA\Property(property="order", type="integer", description="Display order"),
     *             @OA\Property(property="is_private", type="boolean", description="Set as private")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Photo updated successfully")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_profile_photo' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0', 'max:5'],
            'is_private' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $photo = UserPhoto::findOrFail($id);

            // Check if the photo belongs to the authenticated user
            if ($photo->user_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to update this photo'
                ], 403);
            }

            DB::transaction(function() use ($photo, $request, $user) {
                // Handle profile photo logic
                if ($request->has('is_profile_photo') && $request->input('is_profile_photo')) {
                    // Unset existing profile photos
                    UserPhoto::where('user_id', $user->id)
                        ->where('is_profile_photo', true)
                        ->update(['is_profile_photo' => false]);
                }

                // Update the photo
                $photo->update($request->only(['is_profile_photo', 'order', 'is_private']));
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Photo updated successfully',
                'data' => $photo->fresh()
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Photo not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
