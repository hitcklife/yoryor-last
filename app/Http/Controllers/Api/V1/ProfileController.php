<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display a listing of the profiles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        // Check if the user is authorized to view any profiles
        $this->authorize('viewAny', Profile::class);

        $profiles = Profile::paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $profiles
        ]);
    }

    /**
     * Display the specified profile.
     *
     * @param Profile $profile
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function show(Profile $profile)
    {
        // Check if the user is authorized to view the profile
        $this->authorize('view', $profile);

        return response()->json([
            'status' => 'success',
            'data' => $profile
        ]);
    }

    /**
     * Update the specified profile.
     *
     * @param Request $request
     * @param Profile $profile
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Profile $profile)
    {
        // Check if the user is authorized to update the profile
        $this->authorize('update', $profile);

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:50'],
            'last_name' => ['sometimes', 'string', 'max:50'],
            'date_of_birth' => ['sometimes', 'date', 'before:-18 years'],
            'gender' => ['sometimes', 'in:male,female,non-binary,other'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:500'],
            'location' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $profile->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $profile
        ]);
    }

    /**
     * Remove the specified profile.
     *
     * @param Profile $profile
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Profile $profile)
    {
        // Check if the user is authorized to delete the profile
        $this->authorize('delete', $profile);

        $profile->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile deleted successfully'
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myProfile(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile not found',
                'error_code' => 'profile_not_found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $profile
        ]);
    }
}
