<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Program::where('is_active', true)->orderBy('sort_order');

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->boolean('popular')) {
            $query->where('is_popular', true);
        }

        return response()->json($query->get());
    }

    public function show(string $slug): JsonResponse
    {
        $program = Program::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json($program);
    }
}
