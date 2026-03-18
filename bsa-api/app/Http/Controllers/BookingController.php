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

    public function show(string $ref): JsonResponse
    {
        $booking = Booking::where('ref', $ref)->with('items.service')->firstOrFail();

        return response()->json($booking);
    }
}
