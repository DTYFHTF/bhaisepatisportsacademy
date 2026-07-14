<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Trial sessions are a fixed length, unlike court bookings where the
     * customer picks a duration. Used both to block the slot for others and
     * to compute available slots for the trial booking page.
     */
    private const TRIAL_DURATION_MINUTES = 60;

    /**
     * Whether [date, time, time+duration) overlaps any PENDING/CONFIRMED
     * booking already on the books. This is the single source of truth for
     * slot availability — used both to list open slots and, critically, to
     * reject a booking attempt server-side so two customers can never be
     * given the same slot no matter how stale their client's slot list is.
     */
    private function hasOverlap(string $date, string $time, int $duration, ?string $excludeBookingId = null): bool
    {
        [$h, $m] = explode(':', $time);
        $start = ((int) $h) * 60 + (int) $m;
        $end = $start + $duration;

        // whereDate(), not where() — scheduled_date is stored as a full
        // datetime ("2026-08-01 00:00:00"), so a plain string-equality
        // match against "2026-08-01" would silently match nothing.
        $query = Booking::whereDate('scheduled_date', $date)
            ->whereIn('status', ['PENDING', 'CONFIRMED']);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        foreach ($query->get(['scheduled_time', 'total_duration']) as $booking) {
            [$bh, $bm] = explode(':', $booking->scheduled_time);
            $bookedStart = ((int) $bh) * 60 + (int) $bm;
            $bookedEnd = $bookedStart + $booking->total_duration;

            if ($start < $bookedEnd && $end > $bookedStart) {
                return true;
            }
        }

        return false;
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|size:10',
            'customer_email' => 'nullable|email|max:255',
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'required|string',
            'notes'          => 'nullable|string|max:1000',
            'services'       => 'required|array|min:1',
            'services.*'     => 'required|uuid|exists:services,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        return DB::transaction(function () use ($data) {
            $services = Service::whereIn('id', $data['services'])
                ->where('is_active', true)
                ->get();

            if ($services->count() !== count($data['services'])) {
                return response()->json(['message' => 'One or more services are unavailable.'], 422);
            }

            $total = $services->sum('price');
            $totalDuration = $services->sum('duration');

            $booking = Booking::create([
                'ref'            => Booking::generateRef(),
                'customer_name'  => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'scheduled_date' => $data['scheduled_date'],
                'scheduled_time' => $data['scheduled_time'],
                'total_duration' => $totalDuration,
                'total'          => $total,
                'status'         => BookingStatus::PENDING,
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($services as $service) {
                BookingItem::create([
                    'booking_id'   => $booking->id,
                    'service_id'   => $service->id,
                    'service_name' => $service->name,
                    'duration'     => $service->duration,
                    'unit_price'   => $service->price,
                ]);
            }

            return response()->json([
                'booking' => $booking->load('items'),
                'message' => 'Booking created successfully.',
            ], 201);
        });
    }

    public function storeCourtBooking(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name'   => 'required|string|max:255',
            'customer_phone'  => 'required|string|size:10',
            'scheduled_date'  => 'required|date|after:today',
            'scheduled_time'  => 'required|string|regex:/^\d{2}:\d{2}$/',
            'total_duration'  => 'required|integer|in:30,60,90,120',
            'total'           => 'required|integer|min:0',
            'court_preference' => 'nullable|integer|min:1|max:3',
            'experience_level' => 'nullable|in:beginner,intermediate,advanced',
            'goals'           => 'nullable|string|max:500',
            'notes'           => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            return DB::transaction(function () use ($data) {
                if ($this->hasOverlap($data['scheduled_date'], $data['scheduled_time'], $data['total_duration'])) {
                    return response()->json([
                        'message' => 'That time slot was just booked by someone else. Please choose another time.',
                    ], 409);
                }

                $booking = Booking::create([
                    'ref'              => Booking::generateRef(),
                    'customer_name'    => $data['customer_name'],
                    'customer_phone'   => $data['customer_phone'],
                    'scheduled_date'   => $data['scheduled_date'],
                    'scheduled_time'   => $data['scheduled_time'],
                    'total_duration'   => $data['total_duration'],
                    'total'            => $data['total'],
                    'status'           => BookingStatus::PENDING,
                    'experience_level' => $data['experience_level'] ?? null,
                    'goals'            => $data['goals'] ?? null,
                    'notes'            => $data['notes'] ?? null,
                ]);

                return response()->json([
                    'booking' => $booking,
                    'message' => 'Court booking created successfully.',
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Court booking creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create booking.'], 500);
        }
    }

    public function getAvailableSlots(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date'     => 'required|date|after:today',
            'duration' => 'required|integer|in:30,60,90,120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $validator->validated()['date'];
        $duration = $validator->validated()['duration'];

        // Generate all potential slots in 30-minute intervals (6 AM to 8:30 PM)
        $allSlots = [];
        for ($hour = 6; $hour < 21; $hour++) {
            for ($min = 0; $min < 60; $min += 30) {
                $allSlots[] = sprintf('%02d:%02d', $hour, $min);
            }
        }

        $availableSlots = array_values(array_filter(
            $allSlots,
            fn ($slot) => ! $this->hasOverlap($date, $slot, $duration)
        ));

        return response()->json([
            'availableSlots' => $availableSlots,
        ]);
    }

    public function storeTrialBooking(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|size:10',
            'customer_email'   => 'nullable|email|max:255',
            'scheduled_date'   => 'required|date|after:today',
            'scheduled_time'   => 'required|string|regex:/^\d{2}:\d{2}$/',
            'age'              => 'nullable|integer|min:5|max:120',
            'experience_level' => 'required|in:beginner,intermediate,advanced',
            'goals'            => 'nullable|string|max:500',
            'notes'            => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            return DB::transaction(function () use ($data) {
                if ($this->hasOverlap($data['scheduled_date'], $data['scheduled_time'], self::TRIAL_DURATION_MINUTES)) {
                    return response()->json([
                        'message' => 'That time slot was just booked by someone else. Please choose another time.',
                    ], 409);
                }

                $booking = Booking::create([
                    'type'              => 'trial',
                    'ref'               => Booking::generateRef(),
                    'customer_name'     => $data['customer_name'],
                    'customer_phone'    => $data['customer_phone'],
                    'customer_email'    => $data['customer_email'] ?? null,
                    'scheduled_date'    => $data['scheduled_date'],
                    'scheduled_time'    => $data['scheduled_time'],
                    'total_duration'    => self::TRIAL_DURATION_MINUTES,
                    'total'             => 0,
                    'status'            => BookingStatus::PENDING,
                    'age'               => $data['age'] ?? null,
                    'experience_level'  => $data['experience_level'],
                    'goals'             => $data['goals'] ?? null,
                    'notes'             => $data['notes'] ?? null,
                ]);

                return response()->json([
                    'booking' => $booking,
                    'message' => 'Trial booking created successfully.',
                ], 201);
            });
        } catch (\Exception $e) {
            Log::error('Trial booking creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create trial booking.'], 500);
        }
    }

    public function show(string $ref): JsonResponse
    {
        $booking = Booking::where('ref', $ref)->with('items.service')->firstOrFail();

        return response()->json($booking);
    }
}
