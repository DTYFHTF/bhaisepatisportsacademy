<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExchangeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'reason'     => ['required', 'string', 'max:500'],
            'items'      => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'uuid'],
            'items.*.new_size'      => ['required', 'string', 'in:XS,S,M,L,XL,XXL'],
        ]);

        $orderId = $request->get('tracking_order_id');
        $order = Order::findOrFail($orderId);

        if ($order->status->value !== 'DELIVERED') {
            return response()->json([
                'error'   => 'INVALID_STATUS',
                'message' => 'Exchange is only available for delivered orders.',
            ], 422);
        }

        if ($order->exchange_requested) {
            return response()->json([
                'error'   => 'ALREADY_REQUESTED',
                'message' => 'An exchange has already been requested for this order.',
            ], 422);
        }

        // Check 7-day window
        if ($order->updated_at->diffInDays(now()) > 7) {
            return response()->json([
                'error'   => 'WINDOW_EXPIRED',
                'message' => 'The 7-day exchange window has expired.',
            ], 422);
        }

        $order->update([
            'exchange_requested' => true,
            'status'             => 'EXCHANGE_REQUESTED',
        ]);

        $order->statusHistory()->create([
            'status'     => 'EXCHANGE_REQUESTED',
            'note'       => 'Customer exchange request: ' . $request->reason,
            'changed_at' => now(),
        ]);

        return response()->json(['message' => 'Exchange request submitted.']);
    }
}
