<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    /**
     * Get all countries from the database
     *
     * @return JsonResponse
     */
    public function getCountries(): JsonResponse
    {
        $countries = Country::all();

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }
}
