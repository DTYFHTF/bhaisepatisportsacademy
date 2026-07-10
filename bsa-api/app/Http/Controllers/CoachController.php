<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\JsonResponse;

class CoachController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Coach::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    public function show(string $slug): JsonResponse
    {
        $coach = Coach::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json($coach);
    }
}
