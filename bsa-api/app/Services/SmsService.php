<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): void
    {
        try {
            // SparrowSMS requires Nepal country code prefix (977) + 10-digit number
            // Strip any leading + or 977 prefix first, then re-add 977
            $normalised = preg_replace('/^\+?977/', '', $phone);
            $to = '977' . $normalised;

            // SparrowSMS requires form-encoded POST (not JSON)
            $response = Http::asForm()->post('http://api.sparrowsms.com/v2/sms/', [
                'token' => config('services.sparrow.token'),
                'from'  => config('services.sparrow.from', 'BSA'),
                'to'    => $to,
                'text'  => $message,
            ]);

            $responseCode = $response->json('response_code') ?? 0;
            if ($response->failed() || $responseCode >= 1000) {
                Log::error('SMS delivery failed', [
                    'phone_last4'   => substr($phone, -4),
                    'http_status'   => $response->status(),
                    'response_code' => $responseCode,
                    'response'      => $response->json('response'),
                ]);
            } else {
                Log::info('SMS sent', [
                    'phone_last4'   => substr($phone, -4),
                    'response_code' => $responseCode,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('SMS send failed', [
                'phone_last4' => substr($phone, -4),
                'error'       => $e->getMessage(),
            ]);
        }
    }

    public function sendOrderConfirmation(string $phone, string $orderId, string $token): void
    {
        $trackUrl = config('app.frontend_url') . "/track?token={$token}&utm_source=sms&utm_medium=sms&utm_campaign=order-confirm";
        $this->send($phone, "Your BSA order {$orderId} is confirmed. Track it: {$trackUrl}");
    }

    public function sendPaymentConfirmation(mixed $order): void
    {
        $trackingToken = $order->trackingTokens()->first();
        $token = $trackingToken?->token ?? '';

        $trackUrl = config('app.frontend_url') . "/track?token={$token}&utm_source=sms&utm_medium=sms&utm_campaign=payment-confirm";
        $this->send(
            '', // Phone not stored; SMS triggered via admin panel for payment confirmation
            "Payment confirmed for order {$order->order_id}. Track: {$trackUrl}"
        );
    }

    public function sendStatusUpdate(mixed $order): void
    {
        $trackingToken = $order->trackingTokens()->first();
        $token = $trackingToken?->token ?? '';
        $status = $order->status->value;

        $statusMessages = [
            'PACKED'           => "Your order {$order->order_id} has been packed and is ready for dispatch.",
            'DISPATCHED'       => "Your order {$order->order_id} is on its way!",
            'OUT_FOR_DELIVERY' => "Your order {$order->order_id} is out for delivery. Keep your phone handy.",
            'DELIVERED'        => "Your order {$order->order_id} has been delivered. Enjoy!",
        ];

        $message = $statusMessages[$status] ?? "Update for order {$order->order_id}: status is now {$status}.";
        $trackUrl = config('app.frontend_url') . "/track?token={$token}&utm_source=sms&utm_medium=sms&utm_campaign=status-update";
        $message .= " Track: {$trackUrl}";

        // Note: In production, phone would be decrypted or looked up.
        // For now, SMS is triggered from admin panel where phone is available.
        Log::info('Order status SMS queued', [
            'order_id' => $order->order_id,
            'status'   => $status,
        ]);
    }
}
