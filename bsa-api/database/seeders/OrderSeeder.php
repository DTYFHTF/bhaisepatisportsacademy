<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TrackingToken;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private function phoneHash(string $phone): string
    {
        return hash_hmac('sha256', $phone, config('app.key'));
    }

    private function variant(Product $product, string $label): ProductVariant
    {
        return $product->variants()->where('label', $label)->firstOrFail();
    }

    private function addHistory(Order $order, array $statuses): void
    {
        $total = count($statuses);
        foreach ($statuses as $i => [$status, $note]) {
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $status,
                'note'       => $note,
                'changed_by' => $i === 0 ? 'System' : 'Admin',
                'changed_at' => now()->subDays($total - $i - 1)->subHours(rand(0, 8)),
            ]);
        }
    }

    public function run(): void
    {
        $aloeGel = Product::where('slug', 'aloe-vera-soothing-gel')->firstOrFail();
        $preWax  = Product::where('slug', 'pre-wax-cleansing-oil')->firstOrFail();
        $ingrown = Product::where('slug', 'ingrown-hair-serum')->firstOrFail();
        $lotion  = Product::where('slug', 'moisturizing-body-lotion')->firstOrFail();
        $cooling = Product::where('slug', 'post-wax-cooling-spray')->firstOrFail();
        $ricaKit = Product::where('slug', 'at-home-wax-kit-rica')->firstOrFail();

        $fee = 10000; // NPR 100 in paisa

        // ── Order 1: DELIVERED (COD) ──────────────────────────────────────
        $subtotal1 = $aloeGel->price + $cooling->price;
        $order1 = Order::create([
            'order_id'       => 'PP-2603-0001',
            'phone_hash'     => $this->phoneHash('9841234567'),
            'customer_name'  => 'Aarav Sharma',
            'address'        => 'Lazimpat, Kathmandu',
            'city'           => 'Kathmandu',
            'delivery_note'  => 'Ring the bell twice',
            'subtotal'       => $subtotal1,
            'delivery_fee'   => $fee,
            'total'          => $subtotal1 + $fee,
            'payment_method' => PaymentMethod::COD,
            'status'         => OrderStatus::DELIVERED,
        ]);
        $order1->items()->createMany([
            ['product_id' => $aloeGel->id, 'variant_id' => $this->variant($aloeGel, '100ml')->id, 'quantity' => 1, 'unit_price' => $aloeGel->price],
            ['product_id' => $cooling->id, 'variant_id' => $this->variant($cooling, '100ml')->id, 'quantity' => 1, 'unit_price' => $cooling->price],
        ]);
        $this->addHistory($order1, [
            [OrderStatus::PENDING,    'Order placed (COD).'],
            [OrderStatus::CONFIRMED,  'Order confirmed.'],
            [OrderStatus::PACKED,     'Packed and ready for dispatch.'],
            [OrderStatus::DISPATCHED, 'Dispatched via courier.'],
            [OrderStatus::DELIVERED,  'Delivered successfully.'],
        ]);
        TrackingToken::create([
            'token'      => 'DLVR0001',
            'order_id'   => $order1->id,
            'expires_at' => now()->addDays(30),
        ]);

        // ── Order 2: DISPATCHED (Khalti) ──────────────────────────────────
        $subtotal2 = $ricaKit->price;
        $order2 = Order::create([
            'order_id'       => 'PP-2603-0002',
            'phone_hash'     => $this->phoneHash('9852345678'),
            'customer_name'  => 'Priya Thapa',
            'address'        => 'Patan Dhoka, Lalitpur',
            'city'           => 'Lalitpur',
            'subtotal'       => $subtotal2,
            'delivery_fee'   => $fee,
            'total'          => $subtotal2 + $fee,
            'payment_method' => PaymentMethod::KHALTI,
            'payment_ref'    => 'KHL-TXN-2603-ABC123',
            'status'         => OrderStatus::DISPATCHED,
        ]);
        $order2->items()->create([
            'product_id' => $ricaKit->id,
            'variant_id' => $this->variant($ricaKit, 'Standard')->id,
            'quantity'   => 1,
            'unit_price' => $ricaKit->price,
        ]);
        $this->addHistory($order2, [
            [OrderStatus::PENDING,    'Awaiting Khalti payment.'],
            [OrderStatus::CONFIRMED,  'Khalti payment confirmed (ref KHL-TXN-2603-ABC123).'],
            [OrderStatus::PACKED,     'Items packed.'],
            [OrderStatus::DISPATCHED, 'Dispatched.'],
        ]);
        TrackingToken::create([
            'token'      => 'DSPT0002',
            'order_id'   => $order2->id,
            'expires_at' => now()->addDays(7),
        ]);

        // ── Order 3: CONFIRMED (COD) ──────────────────────────────────────
        $subtotal3 = $ingrown->price + $preWax->price;
        $order3 = Order::create([
            'order_id'       => 'PP-2603-0003',
            'phone_hash'     => $this->phoneHash('9863456789'),
            'customer_name'  => 'Rohan Maharjan',
            'address'        => 'New Baneshwor, Kathmandu',
            'city'           => 'Kathmandu',
            'subtotal'       => $subtotal3,
            'delivery_fee'   => $fee,
            'total'          => $subtotal3 + $fee,
            'payment_method' => PaymentMethod::COD,
            'status'         => OrderStatus::CONFIRMED,
        ]);
        $order3->items()->createMany([
            ['product_id' => $ingrown->id, 'variant_id' => $this->variant($ingrown, '30ml')->id, 'quantity' => 1, 'unit_price' => $ingrown->price],
            ['product_id' => $preWax->id,  'variant_id' => $this->variant($preWax, '150ml')->id, 'quantity' => 1, 'unit_price' => $preWax->price],
        ]);
        $this->addHistory($order3, [
            [OrderStatus::PENDING,   'Order placed (COD).'],
            [OrderStatus::CONFIRMED, 'Order confirmed.'],
        ]);
        TrackingToken::create([
            'token'      => 'CONF0003',
            'order_id'   => $order3->id,
            'expires_at' => now()->addDays(7),
        ]);

        // ── Order 4: PACKED (COD, multiple items) ─────────────────────────
        $subtotal4 = $lotion->price * 2 + $aloeGel->price;
        $order4 = Order::create([
            'order_id'       => 'PP-2603-0004',
            'phone_hash'     => $this->phoneHash('9874567890'),
            'customer_name'  => 'Sita Poudel',
            'address'        => 'Boudha, Kathmandu',
            'city'           => 'Kathmandu',
            'subtotal'       => $subtotal4,
            'delivery_fee'   => 0,
            'total'          => $subtotal4,
            'payment_method' => PaymentMethod::COD,
            'status'         => OrderStatus::PACKED,
        ]);
        $order4->items()->createMany([
            ['product_id' => $lotion->id,  'variant_id' => $this->variant($lotion, '200ml')->id,  'quantity' => 2, 'unit_price' => $lotion->price],
            ['product_id' => $aloeGel->id, 'variant_id' => $this->variant($aloeGel, '200ml')->id, 'quantity' => 1, 'unit_price' => 150000],
        ]);
        $this->addHistory($order4, [
            [OrderStatus::PENDING,   'Order placed (COD).'],
            [OrderStatus::CONFIRMED, 'Order confirmed.'],
            [OrderStatus::PACKED,    'Items packed and labelled.'],
        ]);
        TrackingToken::create([
            'token'      => 'PACK0004',
            'order_id'   => $order4->id,
            'expires_at' => now()->addDays(7),
        ]);

        // ── Order 5: CANCELLED ────────────────────────────────────────────
        $subtotal5 = $ricaKit->price;
        $order5 = Order::create([
            'order_id'       => 'PP-2603-0005',
            'phone_hash'     => $this->phoneHash('9896789012'),
            'customer_name'  => 'Anita Rai',
            'address'        => 'Chabahil, Kathmandu',
            'city'           => 'Kathmandu',
            'subtotal'       => $subtotal5,
            'delivery_fee'   => $fee,
            'total'          => $subtotal5 + $fee,
            'payment_method' => PaymentMethod::COD,
            'status'         => OrderStatus::CANCELLED,
        ]);
        $order5->items()->create([
            'product_id' => $ricaKit->id,
            'variant_id' => $this->variant($ricaKit, 'Standard')->id,
            'quantity'   => 1,
            'unit_price' => $ricaKit->price,
        ]);
        $this->addHistory($order5, [
            [OrderStatus::PENDING,   'Order placed (COD).'],
            [OrderStatus::CONFIRMED, 'Order confirmed.'],
            [OrderStatus::CANCELLED, 'Cancelled by customer request.'],
        ]);
    }
}
