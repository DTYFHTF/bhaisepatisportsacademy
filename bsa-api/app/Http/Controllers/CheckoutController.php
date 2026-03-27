<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Services\EsewaService;
use App\Services\KhaltiService;
use App\Services\OrderService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private KhaltiService $khaltiService,
        private EsewaService $esewaService,
    ) {}

    public function place(PlaceOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['payment_method'] === 'KHALTI') {
            // Create order first (Khalti callback can reach our server)
            $order = $this->orderService->place($data);
            $paymentUrl = $this->khaltiService->initiate($order);

            return response()->json([
                'order_id'    => $order->order_id,
                'payment_url' => $paymentUrl,
            ]);
        }

        if ($data['payment_method'] === 'ESEWA') {
            // Don't create order yet - hold data in cache until eSewa confirms
            $pendingKey = Str::uuid()->toString();
            Cache::put("esewa_pending:{$pendingKey}", $data, now()->addMinutes(30));

            $paymentUrl = $this->esewaService->initiateFromPending($pendingKey, $data);

            return response()->json([
                'order_id'    => null,
                'payment_url' => $paymentUrl,
            ]);
        }

        // COD - create immediately
        $order = $this->orderService->place($data);

        return response()->json([
            'order_id' => $order->order_id,
            'status'   => 'CONFIRMED',
        ], 201);
    }

    public function khaltiCallback(Request $request): RedirectResponse
    {
        $orderId = $request->query('purchase_order_id');
        $pidx = $request->query('pidx');

        $order = Order::where('order_id', $orderId)->firstOrFail();

        if ($this->khaltiService->verify($pidx)) {
            $order->update(['status' => 'PAYMENT_CONFIRMED', 'payment_ref' => $pidx]);
            $order->statusHistory()->create([
                'status'     => 'PAYMENT_CONFIRMED',
                'note'       => "Khalti payment verified (pidx: {$pidx})",
                'changed_at' => now(),
            ]);
            app(SmsService::class)->sendOrderConfirmation(
                '',
                $order->order_id,
                $order->trackingTokens()->first()?->token ?? ''
            );

            return redirect(config('app.frontend_url') . "/order/confirmed?id={$orderId}&payment=KHALTI");
        }

        return redirect(config('app.frontend_url') . "/checkout?error=payment_failed&id={$orderId}");
    }

    public function esewaCallback(Request $request): RedirectResponse
    {
        // Failure redirect
        if ($request->query('status') === 'failure') {
            // No order was created yet for eSewa - nothing to cancel
            return redirect(config('app.frontend_url') . '/checkout?error=payment_failed');
        }

        // V2: eSewa sends base64-encoded data
        $encodedData = $request->query('data');
        if (! $encodedData) {
            return redirect(config('app.frontend_url') . '/checkout?error=payment_failed');
        }

        $decoded = $this->esewaService->verify($encodedData);
        if (! $decoded) {
            return redirect(config('app.frontend_url') . '/checkout?error=payment_failed');
        }

        $pendingKey = $decoded['transaction_uuid'] ?? '';
        $transactionCode = $decoded['transaction_code'] ?? '';

        // Retrieve pending order data from cache
        $pendingData = Cache::pull("esewa_pending:{$pendingKey}");
        if (! $pendingData) {
            // Already processed or expired
            return redirect(config('app.frontend_url') . '/checkout?error=payment_failed');
        }

        // Now create the order since payment is confirmed
        $order = $this->orderService->place($pendingData);
        $order->update([
            'status'      => 'PAYMENT_CONFIRMED',
            'payment_ref' => $transactionCode,
        ]);
        $order->statusHistory()->create([
            'status'     => 'PAYMENT_CONFIRMED',
            'note'       => "eSewa payment verified (code: {$transactionCode})",
            'changed_at' => now(),
        ]);

        return redirect(config('app.frontend_url') . "/order/confirmed?id={$order->order_id}&payment=ESEWA");
    }
}
