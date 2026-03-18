# 10 — Backend Architecture

## Overview

The backend is a standalone **Laravel 11** application serving a REST API consumed by the Nuxt frontend. It handles all business logic: OTP auth, payment processing, order management, SMS notifications, and rate limiting.

**Admin panel:** Filament 3 is installed within the same Laravel app and served at `/admin`. See [12-admin-panel.md](12-admin-panel.md).

**Architecture:**
```
Nuxt (frontend) ──REST API──► Laravel (api.bsa.example.com)
                                   │
                              PostgreSQL (Supabase)
                              Cloudinary (images)
                              Sparrow SMS (notifications)
                              Khalti / eSewa (payments)
```

---

## Project Structure (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ProductController.php
│   │   ├── CheckoutController.php
│   │   ├── OtpController.php
│   │   ├── OrderController.php
│   │   ├── RestockController.php
│   │   └── ExchangeController.php
│   ├── Middleware/
│   │   ├── TrackingTokenAuth.php
│   │   └── CheckoutRateLimit.php
│   └── Requests/
│       ├── PlaceOrderRequest.php
│       ├── SendOtpRequest.php
│       └── DeliveryRequest.php
├── Models/
│   ├── Product.php
│   ├── ProductVariant.php
│   ├── ProductImage.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── OrderStatusHistory.php
│   ├── OtpCode.php
│   ├── TrackingToken.php
│   ├── RestockAlert.php
│   ├── Look.php
│   └── LookItem.php
├── Services/
│   ├── OtpService.php
│   ├── OrderService.php
│   ├── SmsService.php
│   ├── KhaltiService.php
│   ├── EsewaService.php
│   └── SizingService.php
└── Filament/
    └── Resources/              # Admin panel resources (see doc 12)
```

---

## API Route Map

Defined in `routes/api.php`:

| Method | Path | Description | Auth |
|---|---|---|---|
| GET | `/api/products` | List products (with filters) | Public |
| GET | `/api/products/{slug}` | Single product by slug | Public |
| POST | `/api/otp/send` | Send OTP to phone | Public + Rate limit |
| POST | `/api/otp/verify` | Verify OTP, return Sanctum token | Public |
| POST | `/api/checkout/initiate` | Create pending order, reserve stock | Public |
| POST | `/api/checkout/place` | Place COD order | Public |
| GET | `/api/checkout/khalti/callback` | Khalti payment callback | HMAC signed |
| POST | `/api/checkout/esewa/callback` | eSewa payment callback | HMAC signed |
| GET | `/api/orders/{id}` | Get order (with tracking token) | Token auth |
| POST | `/api/restock` | Register restock alert | Public + Rate limit |
| POST | `/api/exchange` | Submit exchange/return request | Token auth |

---

## OTP Service (Laravel)

```php
// app/Services/OtpService.php

class OtpService
{
    public function send(string $phone): void
    {
        // Rate check: max 5 OTPs per phone per hour
        $recentCount = OtpCode::where('phone_hash', $this->hashPhone($phone))
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentCount >= 5) {
            throw new TooManyRequestsException();
        }

        $otp = random_int(100000, 999999);
        $codeHash = $this->hashOtp($otp, $phone);

        OtpCode::create([
            'phone_hash' => $this->hashPhone($phone),
            'code_hash'  => $codeHash,
            'expires_at' => now()->addMinutes(10),
        ]);

        app(SmsService::class)->send(
            $phone,
            "Your BSA verification code: {$otp}. Valid for 10 minutes."
        );
    }

    public function verify(string $phone, string $otp): string
    {
        $record = OtpCode::where('phone_hash', $this->hashPhone($phone))
            ->where('code_hash', $this->hashOtp($otp, $phone))
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (!$record) {
            throw new InvalidOtpException();
        }

        $record->update(['used_at' => now()]);

        // Return a Sanctum token scoped to checkout
        $tokenResult = PersonalAccessToken::create([
            'name'      => 'checkout',
            'phone_hash' => $this->hashPhone($phone),
            'expires_at' => now()->addHours(2),
        ]);

        return $tokenResult->plainTextToken;
    }

    private function hashPhone(string $phone): string
    {
        return hash_hmac('sha256', $phone, config('app.otp_secret'));
    }

    private function hashOtp(int $otp, string $phone): string
    {
        return hash_hmac('sha256', "{$otp}:{$phone}", config('app.otp_secret'));
    }
}
```

---

## Order Service (Laravel)

```php
// app/Services/OrderService.php

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
            $subtotal    = collect($input['items'])->sum(fn($i) => $i['price'] * $i['quantity']);
            $deliveryFee = $this->calculateDeliveryFee($input['city'], $subtotal);
            $total       = $subtotal + $deliveryFee;

            // 4. Create order
            $order = Order::create([
                'order_id'      => $this->generateOrderId(),
                'phone_hash'    => hash_hmac('sha256', $input['phone'], config('app.otp_secret')),
                'customer_name' => $input['name'],
                'address'       => $input['address'],
                'city'          => $input['city'],
                'delivery_note' => $input['delivery_note'] ?? null,
                'subtotal'      => $subtotal,
                'delivery_fee'  => $deliveryFee,
                'total'         => $total,
                'payment_method' => $input['payment_method'],
                'status'        => $input['payment_method'] === 'COD' ? 'CONFIRMED' : 'PENDING_PAYMENT',
            ]);

            foreach ($input['items'] as $item) {
                $order->items()->create([
                    'product_id'  => $item['product_id'],
                    'variant_id'  => $item['variant_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['price'],
                ]);
            }

            // 5. Create tracking token
            $token = TrackingToken::create([
                'token'      => Str::random(8),
                'order_id'   => $order->id,
                'expires_at' => now()->addDays(30),
            ]);

            // 6. Send confirmation SMS
            if ($input['payment_method'] === 'COD') {
                app(SmsService::class)->sendOrderConfirmation(
                    $input['phone'], $order->order_id, $token->token
                );
            }

            return $order;
        });
    }
}
```

`lockForUpdate()` acquires a PostgreSQL row-level lock — this is what prevents overselling during concurrent drop launches.

---

## Khalti Integration (Laravel)

```php
// app/Services/KhaltiService.php

class KhaltiService
{
    private string $apiBase = 'https://a.khalti.com/api/v2';

    public function initiate(Order $order): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.khalti.secret'),
        ])->post("{$this->apiBase}/epayment/initiate/", [
            'return_url'           => config('app.url') . '/api/checkout/khalti/callback',
            'website_url'          => config('app.frontend_url'),
            'amount'               => $order->total * 100, // paisa
            'purchase_order_id'    => $order->order_id,
            'purchase_order_name'  => "Bhaisepati Sports Academy Order {$order->order_id}",
        ]);

        if ($response->failed()) {
            throw new PaymentInitiationException($response->json('detail'));
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
```

**Callback route (`GET /api/checkout/khalti/callback`):**

```php
public function khaltiCallback(Request $request): RedirectResponse
{
    $orderId = $request->query('purchase_order_id');
    $pidx    = $request->query('pidx');

    $order = Order::where('order_id', $orderId)->firstOrFail();

    if (app(KhaltiService::class)->verify($pidx)) {
        $order->update(['status' => 'PAYMENT_CONFIRMED', 'payment_ref' => $pidx]);
        app(SmsService::class)->sendPaymentConfirmation($order);

        return redirect(config('app.frontend_url') . "/order/confirmed?id={$orderId}");
    }

    return redirect(config('app.frontend_url') . "/checkout?error=payment_failed&id={$orderId}");
}
```

---

## SMS Service (Laravel)

```php
// app/Services/SmsService.php

class SmsService
{
    public function send(string $phone, string $message): void
    {
        try {
            Http::post('https://api.sparrowsms.com/v2/sms/', [
                'token'  => config('services.sparrow.token'),
                'from'   => config('services.sparrow.from'), // BSAWears
                'to'     => $phone,
                'text'   => $message,
            ]);
        } catch (\Throwable $e) {
            // Log but never block order completion
            Log::error('SMS send failed', ['phone' => substr($phone, -4), 'error' => $e->getMessage()]);
        }
    }

    public function sendOrderConfirmation(string $phone, string $orderId, string $token): void
    {
        $this->send($phone,
            "Your BSA order {$orderId} is confirmed. Track it: bsa.example.com/t/{$token}"
        );
    }
}
```

---

## Rate Limiting (Laravel)

Laravel has built-in rate limiting via `RateLimiter`:

```php
// app/Providers/AppServiceProvider.php

RateLimiter::for('otp', function (Request $request) {
    return Limit::perHour(5)->by($request->ip());
});

RateLimiter::for('checkout', function (Request $request) {
    return Limit::perMinutes(10, 10)->by($request->ip());
});
```

Applied in `routes/api.php`:
```php
Route::middleware('throttle:otp')->post('/otp/send', [OtpController::class, 'send']);
Route::middleware('throttle:checkout')->post('/checkout/place', [CheckoutController::class, 'place']);
```

No Upstash/Redis required — Laravel's cache-backed rate limiter (using database or file driver) is sufficient at this scale.

---

## Error Handling

All API controllers return consistent JSON error responses:

```php
// app/Exceptions/Handler.php

public function register(): void
{
    $this->renderable(function (OutOfStockException $e) {
        return response()->json([
            'error'   => 'OUT_OF_STOCK',
            'message' => 'This size is no longer available.',
        ], 409);
    });

    $this->renderable(function (InvalidOtpException $e) {
        return response()->json([
            'error'   => 'INVALID_OTP',
            'message' => 'Invalid or expired code.',
        ], 422);
    });

    $this->renderable(function (ValidationException $e) {
        return response()->json([
            'error'  => 'VALIDATION_ERROR',
            'fields' => $e->errors(),
        ], 400);
    });
}
