<?php

namespace App\Services;

use App\Exceptions\PaymentInitiationException;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class KhaltiService
{
    private string $apiBase = 'https://a.khalti.com/api/v2';

    public function initiate(Order $order): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.khalti.secret'),
        ])->post("{$this->apiBase}/epayment/initiate/", [
            'return_url'          => config('app.url') . '/api/checkout/khalti/callback',
            'website_url'         => config('app.frontend_url'),
            'amount'              => $order->total * 100, // paisa
            'purchase_order_id'   => $order->order_id,
            'purchase_order_name' => "BSA Order {$order->order_id}",
        ]);

        if ($response->failed()) {
            throw new PaymentInitiationException($response->json('detail', 'Khalti initiation failed.'));
        }

        return $response->json('payment_url');
    }

    public function verify(string $pidx): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.khalti.secret'),
        ])->post("{$this->apiBase}/epayment/lookup/", ['pidx' => $pidx]);

        return $response->json('status') === 'Completed';
    }
}
