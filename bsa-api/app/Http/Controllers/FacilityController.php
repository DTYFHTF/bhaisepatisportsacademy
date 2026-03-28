<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\JsonResponse;

class FacilityController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Facility::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    public function show(string $slug): JsonResponse
    {
        $facility = Facility::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json($facility);
    }
}
