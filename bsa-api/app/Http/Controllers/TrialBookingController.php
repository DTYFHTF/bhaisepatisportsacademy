<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\TrialBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrialBookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'trial_type'     => 'required|string|in:badminton,gym,sauna',
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|size:10',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required|string',
            'duration'       => 'required|integer|min:30|max:180',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $trialBooking = TrialBooking::create([
            'ref'            => TrialBooking::generateRef(),
            'trial_type'     => $data['trial_type'],
            'customer_name'  => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'],
            'duration'       => $data['duration'],
            'status'         => BookingStatus::PENDING,
            'notes'          => $data['notes'] ?? null,
        ]);

        return response()->json([
            'trial_booking' => $trialBooking,
            'message'       => 'Trial booking created successfully.',
        ], 201);
    }

    public function show(string $ref): JsonResponse
    {
        $trialBooking = TrialBooking::where('ref', $ref)->firstOrFail();
        return response()->json($trialBooking);
    }

    public function index(): JsonResponse
    {
        $trialBookings = TrialBooking::orderBy('created_at', 'desc')->get();
        return response()->json($trialBookings);
    }
}