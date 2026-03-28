<?php

namespace App\Http\Controllers;

use App\Models\ScheduleSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ScheduleSlot::where('is_active', true)->orderBy('sort_order');

        if ($request->has('day')) {
            $query->where('day', $request->input('day'));
        }

        return response()->json($query->get());
    }
}
