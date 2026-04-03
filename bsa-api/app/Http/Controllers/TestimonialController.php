<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Testimonial::where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'role'  => 'nullable|string|max:100',
            'quote' => 'required|string|min:20|max:500',
        ]);

        Testimonial::create([
            'name'       => $data['name'],
            'role'       => $data['role'] ?? 'BSA Member',
            'quote'      => $data['quote'],
            'is_active'  => false,    // pending admin approval
            'sort_order' => 0,
        ]);

        return response()->json(['message' => 'Thank you! Your testimonial is pending review.'], 201);
    }
}
