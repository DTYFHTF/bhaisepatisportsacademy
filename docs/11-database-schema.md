# 11 — Database Schema

## Technology

- **Database:** PostgreSQL 15
- **Hosted:** Supabase (free tier → Pro when >500MB or >50K rows)
- **ORM:** Eloquent (built into Laravel 11)
- **Migrations:** Laravel Schema Builder (`database/migrations/`)
- **Connection pooling:** PgBouncer via Supabase (configure `DB_URL` with pgbouncer mode)

---

## Enums (PHP 8.1)

```php
// app/Enums/Category.php
enum Category: string {
    case JACKET    = 'JACKET';
    case TOP       = 'TOP';
    case BOTTOM    = 'BOTTOM';
    case ACCESSORY = 'ACCESSORY';
}

// app/Enums/Size.php
enum Size: string {
    case XS = 'XS'; case S = 'S'; case M = 'M';
    case L  = 'L';  case XL = 'XL'; case XXL = 'XXL';
}

// app/Enums/WardrobeRole.php
enum WardrobeRole: string {
    case OUTERWEAR  = 'outerwear';
    case MIDLAYER   = 'midlayer';
    case TOP        = 'top';
    case BOTTOM     = 'bottom';
    case ACCESSORY  = 'accessory';
}

// app/Enums/PaymentMethod.php
enum PaymentMethod: string {
    case KHALTI = 'KHALTI';
    case ESEWA  = 'ESEWA';
    case COD    = 'COD';
}

// app/Enums/OrderStatus.php
enum OrderStatus: string {
    case PENDING_PAYMENT    = 'PENDING_PAYMENT';
    case PAYMENT_CONFIRMED  = 'PAYMENT_CONFIRMED';
    case CONFIRMED          = 'CONFIRMED';
    case PACKED             = 'PACKED';
    case DISPATCHED         = 'DISPATCHED';
    case OUT_FOR_DELIVERY   = 'OUT_FOR_DELIVERY';
    case DELIVERED          = 'DELIVERED';
    case CANCELLED          = 'CANCELLED';
    case EXCHANGE_REQUESTED = 'EXCHANGE_REQUESTED';
    case RETURNED           = 'RETURNED';
}
```

---

## Migrations

### Products

```php
// database/migrations/2025_01_01_000001_create_products_table.php

Schema::create('products', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('slug')->unique();
    $table->string('name');
    $table->string('color_name');
    $table->unsignedInteger('price');           // NPR
    $table->unsignedInteger('compare_at_price')->nullable();
    $table->string('category');                 // Category enum value
    $table->text('description');
    $table->text('fabric_story');
    $table->string('wardrobe_role')->nullable(); // WardrobeRole enum value
    $table->json('tags')->default('[]');
    $table->boolean('is_active')->default(true);
    $table->string('seo_title')->nullable();
    $table->string('seo_description')->nullable();
    $table->timestamps();

    $table->index('category');
    $table->index('is_active');
});

// Pivot table for "complete the look" product pairs
Schema::create('product_pairs', function (Blueprint $table) {
    $table->uuid('product_id');
    $table->uuid('paired_with_id');
    $table->primary(['product_id', 'paired_with_id']);
    $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
    $table->foreign('paired_with_id')->references('id')->on('products')->cascadeOnDelete();
});

// database/migrations/2025_01_01_000002_create_product_variants_table.php
Schema::create('product_variants', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->uuid('product_id');
    $table->string('size');                     // Size enum value
    $table->string('sku')->unique();
    $table->unsignedInteger('stock')->default(0);
    $table->unsignedInteger('reserved_stock')->default(0);
    $table->timestamps();

    $table->unique(['product_id', 'size']);
    $table->index('product_id');
    $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
});

// database/migrations/2025_01_01_000003_create_product_images_table.php
Schema::create('product_images', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->uuid('product_id');
    $table->string('cloudinary_id');
    $table->string('url');
    $table->string('alt_text')->nullable();
    $table->unsignedInteger('order')->default(0);
    $table->timestamps();

    $table->index('product_id');
    $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
});
```

### Orders

```php
// database/migrations/2025_01_01_000004_create_orders_table.php

Schema::create('orders', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('order_id')->unique();       // DH-YYMM-XXXX
    $table->string('phone_hash');               // sha256(phone + secret)
    $table->string('customer_name');
    $table->string('address');
    $table->string('city');
    $table->text('delivery_note')->nullable();
    $table->unsignedInteger('subtotal');
    $table->unsignedInteger('delivery_fee');
    $table->unsignedInteger('total');
    $table->string('payment_method');           // PaymentMethod enum value
    $table->string('payment_ref')->nullable();  // Khalti/eSewa transaction ID
    $table->string('status')->default('PENDING_PAYMENT');
    $table->boolean('exchange_requested')->default(false);
    $table->timestamps();

    $table->index('phone_hash');
    $table->index('status');
    $table->index('created_at');
});

// database/migrations/2025_01_01_000005_create_order_items_table.php
Schema::create('order_items', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->uuid('order_id');
    $table->uuid('product_id');
    $table->uuid('variant_id');
    $table->unsignedInteger('quantity');
    $table->unsignedInteger('unit_price');

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
    $table->foreign('product_id')->references('id')->on('products');
    $table->foreign('variant_id')->references('id')->on('product_variants');
    $table->index('order_id');
});

// database/migrations/2025_01_01_000006_create_order_status_histories_table.php
Schema::create('order_status_histories', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->uuid('order_id');
    $table->string('status');
    $table->text('note')->nullable();
    $table->string('changed_by')->nullable();   // Admin user email
    $table->timestamp('changed_at')->useCurrent();

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
    $table->index('order_id');
});
```

### Auth / OTP

```php
// database/migrations/2025_01_01_000007_create_otp_codes_table.php

Schema::create('otp_codes', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('phone_hash');
    $table->string('code_hash');
    $table->timestamp('expires_at');
    $table->timestamp('used_at')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index('phone_hash');
    $table->index('expires_at');
});

// database/migrations/2025_01_01_000008_create_tracking_tokens_table.php
Schema::create('tracking_tokens', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('token', 8)->unique(); // 8-char alphanumeric
    $table->uuid('order_id');
    $table->timestamp('expires_at');
    $table->timestamp('created_at')->useCurrent();

    $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
    $table->index('token');
});
```

### Wardrobe / Looks

```php
// database/migrations/2025_01_01_000009_create_looks_table.php

Schema::create('looks', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('look_hash', 8)->unique();   // URL-safe 8-char hash
    $table->string('display_name')->nullable();
    $table->string('phone_hash')->nullable();
    $table->text('ai_explanation')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index('phone_hash');
});

Schema::create('look_items', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->uuid('look_id');
    $table->uuid('product_id');
    $table->unsignedInteger('order')->default(0);

    $table->foreign('look_id')->references('id')->on('looks')->cascadeOnDelete();
    $table->foreign('product_id')->references('id')->on('products');
    $table->index('look_id');
});
```

### Restock Alerts

```php
// database/migrations/2025_01_01_000010_create_restock_alerts_table.php

Schema::create('restock_alerts', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('phone_hash');
    $table->uuid('product_id');
    $table->uuid('variant_id')->nullable();     // null = any size
    $table->boolean('notified')->default(false);
    $table->timestamp('notified_at')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->unique(['phone_hash', 'variant_id']); // One alert per phone per size
    $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
    $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete();
    $table->index('product_id');
    $table->index('notified');
});
```

---

## Eloquent Models

```php
// app/Models/Product.php

class Product extends Model
{
    use HasUuids;

    protected $casts = [
        'tags'          => 'array',
        'category'      => Category::class,
        'wardrobe_role' => WardrobeRole::class,
        'is_active'     => 'boolean',
    ];

    public function variants(): HasMany     { return $this->hasMany(ProductVariant::class); }
    public function images(): HasMany       { return $this->hasMany(ProductImage::class)->orderBy('order'); }
    public function orderItems(): HasMany   { return $this->hasMany(OrderItem::class); }
    public function restockAlerts(): HasMany { return $this->hasMany(RestockAlert::class); }
    public function looks(): BelongsToMany  { return $this->belongsToMany(Look::class, 'look_items'); }

    public function pairedWith(): BelongsToMany {
        return $this->belongsToMany(Product::class, 'product_pairs', 'product_id', 'paired_with_id');
    }
}

// app/Models/Order.php
class Order extends Model
{
    use HasUuids;

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status'         => OrderStatus::class,
    ];

    public function items(): HasMany         { return $this->hasMany(OrderItem::class); }
    public function statusHistory(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
    public function trackingTokens(): HasMany { return $this->hasMany(TrackingToken::class); }
}

// app/Models/ProductVariant.php
class ProductVariant extends Model
{
    use HasUuids;

    protected $casts = ['size' => Size::class];

    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
    public function orderItems(): HasMany   { return $this->hasMany(OrderItem::class); }

    public function getAvailableAttribute(): int
    {
        return $this->stock - $this->reserved_stock;
    }
}
```

---

## Indexing Strategy

| Index | Reason |
|---|---|
| `orders.phone_hash` | Look up all orders by phone number |
| `orders.status` | Admin filters by status |
| `orders.created_at` | Sort and paginate orders |
| `order_items.order_id` | Join to order |
| `product_variants.product_id` | Product page variant lookup |
| `otp_codes.phone_hash + expires_at` | OTP verification query |
| `restock_alerts.product_id + notified` | Batch notify when restocked |
| `tracking_tokens.token` | Short-token redirect lookup |

---

## Seed Data

```php
// database/seeders/ProductSeeder.php

public function run(): void
{
    $fieldJacket = Product::create([
        'slug'          => 'field-jacket-olive',
        'name'          => 'Field Jacket',
        'color_name'    => 'Olive',
        'price'         => 5500,
        'category'      => Category::JACKET,
        'wardrobe_role' => WardrobeRole::OUTERWEAR,
        'description'   => 'A minimal field jacket cut for Kathmandu seasons.',
        'fabric_story'  => 'Substantial cotton-nylon shell...',
        'tags'          => ['jacket', 'new-arrival', 'bestseller'],
    ]);

    foreach ([['S',8], ['M',12], ['L',10], ['XL',5]] as [$size, $stock]) {
        $fieldJacket->variants()->create([
            'size'  => Size::from($size),
            'sku'   => "DH-FJ-OLV-{$size}",
            'stock' => $stock,
        ]);
    }
}
```

Run with:
```bash
php artisan db:seed
```

---

## Migrations Reference

```bash
# Development
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/2025_01_01_000001_create_products_table.php

# Production deploy (via CI/CD)
php artisan migrate --force

# Reset (development only — destroys all data)
php artisan migrate:fresh --seed
```

Never run `migrate:fresh` in production.
