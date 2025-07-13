<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EmergencyContactsController extends Controller
{
    /**
     * Get emergency contacts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmergencyContacts(Request $request): JsonResponse
    {
        $user = $request->user();

        $contacts = $user->emergencyContacts()
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Add emergency contact
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addEmergencyContact(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'relationship' => 'nullable|string|max:100',
            'is_primary' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is set as primary, unset any existing primary contact
        if ($request->is_primary) {
            $user->emergencyContacts()
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        // Create the emergency contact
        $contact = new EmergencyContact([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'relationship' => $request->relationship,
            'is_primary' => $request->is_primary ?? false,
        ]);

        $contact->save();

        return response()->json([
            'success' => true,
            'message' => 'Emergency contact added successfully',
            'data' => $contact
        ]);
    }

    /**
     * Update emergency contact
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateEmergencyContact(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Find the contact
        $contact = $user->emergencyContacts()->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Emergency contact not found'
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'relationship' => 'nullable|string|max:100',
            'is_primary' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is set as primary, unset any existing primary contact
        if ($request->has('is_primary') && $request->is_primary && !$contact->is_primary) {
            $user->emergencyContacts()
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        // Update the contact
        $contact->fill($request->only([
            'name',
            'phone',
            'relationship',
            'is_primary',
        ]));

        $contact->save();

        return response()->json([
            'success' => true,
            'message' => 'Emergency contact updated successfully',
            'data' => $contact
        ]);
    }

    /**
     * Delete emergency contact
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function deleteEmergencyContact(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Find the contact
        $contact = $user->emergencyContacts()->find($id);

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Emergency contact not found'
            ], 404);
        }

        // Delete the contact
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Emergency contact deleted successfully'
        ]);
    }
}
