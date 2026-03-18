<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $orderId = $request->get('tracking_order_id');

        $order = Order::with(['items.product.images', 'items.variant', 'statusHistory'])
            ->find($orderId);

        if (! $order) {
            return response()->json([
                'message' => 'We couldn\'t find that order. Please double-check your tracking link.',
            ], 404);
        }

        return response()->json($order);
    }

    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'phone'    => ['nullable', 'string'],
            'order_id' => ['nullable', 'string'],
        ]);

        // Require at least one identifier
        if (! $request->phone && ! $request->order_id) {
            return response()->json(['message' => 'Please enter a phone number or order ID.'], 422);
        }

        $query = Order::with(['items.product.images', 'items.variant', 'statusHistory']);

        if ($request->order_id) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->phone) {
            $phoneHash = app(OtpService::class)->hashPhone($request->phone);
            $query->where('phone_hash', $phoneHash);
        }

        $order = $query->orderByDesc('created_at')->first();

        if (! $order) {
            $msg = match (true) {
                (bool) $request->order_id && (bool) $request->phone =>
                    "No order found for ID {$request->order_id} with that phone number.",
                (bool) $request->order_id =>
                    "No order found with ID {$request->order_id}.",
                default =>
                    'No order found for this phone number.',
            };

            return response()->json(['message' => $msg], 404);
        }

        return response()->json($order);
    }
}
