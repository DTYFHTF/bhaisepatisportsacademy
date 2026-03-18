<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class EsewaService
{
    private function baseUrl(): string
    {
        return config('services.esewa.sandbox', true)
            ? 'https://rc4.esewa.com.np'
            : 'https://epay.esewa.com.np';
    }

    /**
     * Initiate payment from pending cache key (before order is created).
     * The pending key IS the transaction UUID so eSewa's callback can retrieve it.
     */
    public function initiateFromPending(string $pendingKey, array $data): string
    {
        $productCode = config('services.esewa.merchant_code');
        // Prices stored in paisa; eSewa expects NPR (rupees)
        $totalAmount = (int) round(
            collect($data['items'])->sum(fn ($i) => $i['price'] * $i['quantity']) / 100
        );

        // Add delivery fee (same logic as OrderService)
        $subtotalPaisa = collect($data['items'])->sum(fn ($i) => $i['price'] * $i['quantity']);
        $deliveryFee = $this->calcDeliveryFee($data['city'], $subtotalPaisa);
        $totalAmount = (int) round(($subtotalPaisa + $deliveryFee) / 100);

        $signature = $this->generateSignature($totalAmount, $pendingKey, $productCode);

        $params = [
            'amount'                  => $totalAmount,
            'tax_amount'              => 0,
            'total_amount'            => $totalAmount,
            'transaction_uuid'        => $pendingKey,
            'product_code'            => $productCode,
            'product_service_charge'  => 0,
            'product_delivery_charge' => 0,
            'success_url'             => config('app.url') . '/api/checkout/esewa/callback',
            'failure_url'             => config('app.url') . '/api/checkout/esewa/callback?status=failure',
            'signed_field_names'      => 'total_amount,transaction_uuid,product_code',
            'signature'               => $signature,
        ];

        return $this->baseUrl() . '/api/epay/main/v2/form?' . http_build_query($params);
    }

    public function verify(string $encodedData): array|false
    {
        $decoded = json_decode(base64_decode($encodedData), true);

        if (! $decoded || ($decoded['status'] ?? '') !== 'COMPLETE') {
            return false;
        }

        // Verify signature
        $signedFields = $decoded['signed_field_names'] ?? '';
        $fields = explode(',', $signedFields);
        $input = implode(',', array_map(fn ($f) => "{$f}={$decoded[$f]}", $fields));

        $expectedSig = base64_encode(hash_hmac('sha256', $input, config('services.esewa.secret'), true));

        if (! hash_equals($expectedSig, $decoded['signature'] ?? '')) {
            return false;
        }

        return $decoded;
    }

    private function calcDeliveryFee(string $city, int $subtotalPaisa): int
    {
        if ($subtotalPaisa >= 500000) {
            return 0;
        }
        return in_array(strtolower($city), ['kathmandu', 'lalitpur', 'bhaktapur']) ? 10000 : 15000;
    }

    private function generateSignature(int $totalAmount, string $uuid, string $productCode): string
    {
        $input = "total_amount={$totalAmount},transaction_uuid={$uuid},product_code={$productCode}";

        return base64_encode(hash_hmac('sha256', $input, config('services.esewa.secret'), true));
    }
}
