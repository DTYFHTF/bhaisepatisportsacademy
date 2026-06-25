<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Store a court booking (from book.vue)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'           => 'required|string|in:court,trial',
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|size:10',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required|string',
            'duration'       => 'required|integer|min:30|max:180',
            'court_number'   => 'nullable|integer|min:1|max:10',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Calculate price based on duration
        $pricePerMinute = 50000 / 60; // NPR 500 per hour
        $total = (int) round($pricePerMinute * $data['duration']);

        $booking = Booking::create([
            'type'           => 'court',
            'ref'            => Booking::generateRef(),
            'customer_name'  => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'],
            'court_number'   => $data['court_number'] ?? null,
            'total_duration' => $data['duration'],
            'total'          => $total,
            'status'         => BookingStatus::PENDING,
            'notes'          => $data['notes'] ?? null,
        ]);

        return response()->json([
            'booking' => $booking,
            'message' => 'Court booking created successfully.',
        ], 201);
    }

    public function show(string $ref): JsonResponse
    {
        $booking = Booking::where('ref', $ref)->firstOrFail();
        return response()->json($booking);
    }

    public function index(): JsonResponse
    {
        $bookings = Booking::orderBy('created_at', 'desc')->get();
        return response()->json($bookings);
    }
}