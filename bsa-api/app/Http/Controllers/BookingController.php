<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
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
            'notes'           => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            $booking = Booking::create([
                'ref'              => Booking::generateRef(),
                'customer_name'    => $data['customer_name'],
                'customer_phone'   => $data['customer_phone'],
                'scheduled_date'   => $data['scheduled_date'],
                'scheduled_time'   => $data['scheduled_time'],
                'total_duration'   => $data['total_duration'],
                'total'            => $data['total'],
                'status'           => BookingStatus::PENDING,
                'notes'            => $data['notes'] ?? null,
            ]);

            return response()->json([
                'booking' => $booking,
                'message' => 'Court booking created successfully.',
            ], 201);
        } catch (\Exception $e) {
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

        // Get all confirmed or pending bookings for this date
        $bookedSlots = Booking::where('scheduled_date', $date)
            ->whereIn('status', ['PENDING', 'CONFIRMED'])
            ->select('scheduled_time', 'total_duration')
            ->get();

        // Convert booked slots to minutes from midnight for easier math
        $bookedRanges = $bookedSlots->map(function ($booking) {
            $timeParts = explode(':', $booking->scheduled_time);
            $startMinutes = (int) $timeParts[0] * 60 + (int) $timeParts[1];
            $endMinutes = $startMinutes + $booking->total_duration;
            return ['start' => $startMinutes, 'end' => $endMinutes];
        });

        // Generate all potential slots in 30-minute intervals (6 AM to 8:30 PM)
        $allSlots = [];
        for ($hour = 6; $hour < 21; $hour++) {
            for ($min = 0; $min < 60; $min += 30) {
                $allSlots[] = sprintf('%02d:%02d', $hour, $min);
            }
        }

        // Filter out slots that would overlap with existing bookings
        $availableSlots = array_filter($allSlots, function ($slot) use ($bookedRanges, $duration) {
            $timeParts = explode(':', $slot);
            $slotStartMinutes = (int) $timeParts[0] * 60 + (int) $timeParts[1];
            $slotEndMinutes = $slotStartMinutes + $duration;

            // Check if this slot overlaps with any booked slot
            foreach ($bookedRanges as $booked) {
                // Overlap occurs if: slot start < booked end AND slot end > booked start
                if ($slotStartMinutes < $booked['end'] && $slotEndMinutes > $booked['start']) {
                    return false; // This slot overlaps, exclude it
                }
            }
            return true; // No overlap, include it
        });

        return response()->json([
            'availableSlots' => array_values($availableSlots), // Re-index array
            'bookedSlots'    => $bookedSlots,
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
            $booking = Booking::create([
                'type'              => 'trial',
                'ref'               => Booking::generateRef(),
                'customer_name'     => $data['customer_name'],
                'customer_phone'    => $data['customer_phone'],
                'customer_email'    => $data['customer_email'] ?? null,
                'scheduled_date'    => $data['scheduled_date'],
                'scheduled_time'    => $data['scheduled_time'],
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create trial booking.'], 500);
        }
    }

    public function show(string $ref): JsonResponse
    {
        $booking = Booking::where('ref', $ref)->with('items.service')->firstOrFail();

        return response()->json($booking);
    }
}
