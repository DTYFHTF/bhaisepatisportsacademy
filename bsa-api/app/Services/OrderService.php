<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\TrackingToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function place(array $input): Order
    {
        return DB::transaction(function () use ($input) {
            // 1. Verify and lock stock rows
            foreach ($input['items'] as $item) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($item['variant_id']);
                $available = $variant->stock - $variant->reserved_stock;

                if ($available < $item['quantity']) {
                    throw new OutOfStockException($item['variant_id']);
                }
            }

            // 2. Deduct stock
            foreach ($input['items'] as $item) {
                ProductVariant::where('id', $item['variant_id'])
                    ->decrement('stock', $item['quantity']);
            }

            // 3. Calculate totals
            $subtotal = collect($input['items'])->sum(fn ($i) => $i['price'] * $i['quantity']);
            $deliveryFee = $this->calculateDeliveryFee($input['city'], $subtotal);
            $total = $subtotal + $deliveryFee;

            // 4. Create order
            $order = Order::create([
                'order_id'          => $this->generateOrderId(),
                'phone_hash'        => app(OtpService::class)->hashPhone($input['phone']),
                'phone'             => $input['phone'],
                'customer_name'     => $input['name'],
                'address'           => $input['address'],
                'city'              => $input['city'],
                'delivery_note'     => $input['delivery_note'] ?? null,
                'latitude'          => $input['latitude'] ?? null,
                'longitude'         => $input['longitude'] ?? null,
                'formatted_address' => $input['formatted_address'] ?? null,
                'nearest_landmark'  => $input['nearest_landmark'] ?? null,
                'email'             => $input['email'] ?? null,
                'subtotal'          => $subtotal,
                'delivery_fee'      => $deliveryFee,
                'total'             => $total,
                'payment_method'    => $input['payment_method'],
                'status'            => $input['payment_method'] === 'COD' ? 'CONFIRMED' : 'PENDING_PAYMENT',
            ]);

            // 5. Create order items
            foreach ($input['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['price'],
                ]);
            }

            // 6. Log initial status
            $order->statusHistory()->create([
                'status'     => $order->status->value,
                'note'       => 'Order placed',
                'changed_at' => now(),
            ]);

            // 7. Create tracking token
            $token = TrackingToken::create([
                'token'      => Str::random(8),
                'order_id'   => $order->id,
                'expires_at' => now()->addDays(30),
            ]);

            // 8. Send confirmation SMS for COD
            if ($input['payment_method'] === 'COD') {
                app(SmsService::class)->sendOrderConfirmation(
                    $input['phone'],
                    $order->order_id,
                    $token->token
                );
            }

            return $order->load('items');
        });
    }

    private function calculateDeliveryFee(string $city, int $subtotal): int
    {
        if ($subtotal >= 500000) {
            return 0;
        }

        $valleyCities = ['kathmandu', 'lalitpur', 'bhaktapur'];

        return in_array(strtolower($city), $valleyCities) ? 10000 : 15000;
    }

    private function generateOrderId(): string
    {
        $prefix = 'PP-' . now()->format('ym') . '-';
        $random = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        while (Order::where('order_id', $prefix . $random)->exists()) {
            $random = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        }

        return $prefix . $random;
    }
}
