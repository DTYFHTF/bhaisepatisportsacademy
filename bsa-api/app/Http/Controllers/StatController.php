<?php

namespace App\Http\Controllers;

use App\Models\SiteStat;
use Illuminate\Http\JsonResponse;

class StatController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            SiteStat::where('is_active', true)->orderBy('sort_order')->get()
        );
    }
}
