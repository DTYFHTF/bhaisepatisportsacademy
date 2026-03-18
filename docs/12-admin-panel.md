# 12 — Admin Panel (Filament 3)

## Overview

The admin panel is built with **Filament 3**, a first-party Laravel package that generates a full-featured admin UI from PHP resource classes. It lives at `/admin` on the Laravel backend — no separate frontend build or deployment needed.

Filament uses **Livewire** for reactivity (server-rendered, no client-side JS framework required). The admin is part of the Laravel app, not the Nuxt frontend.

**URL:** `/admin` on the Laravel backend (e.g., `api.bsa.example.com/admin`)  
**Auth:** Filament's built-in email + password guard. Create the first user with:

```bash
php artisan make:filament-user
```

Single admin user in Phase 1; Filament Shield (role-based access) added in Phase 4.

---

## Dashboard

The dashboard is built with Filament's `StatsOverviewWidget`:

```php
// app/Filament/Widgets/StatsOverview.php

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Orders (7 days)', Order::where('created_at', '>=', now()->subDays(7))->count())
                ->color('primary'),

            Stat::make('Revenue (7 days)', 'NPR ' . number_format(
                Order::where('created_at', '>=', now()->subDays(7))
                     ->whereNotIn('status', ['CANCELLED', 'PENDING_PAYMENT'])
                     ->sum('total') / 100
            ))
                ->color('success'),

            Stat::make('Low Stock Items', ProductVariant::whereRaw('stock - reserved_stock <= 3')->count())
                ->color('danger'),
        ];
    }
}
```

Dashboard layout:
```
┌──────────────────────────────────────────────────────────┐
│  BSA ADMIN                               Logout →     │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐              │
│  │  Orders  │  │ Revenue  │  │ Low Stock│              │
│  │    14    │  │ NPR 74K  │  │    3     │              │
│  │ 7-day    │  │ 7-day    │  │ items    │              │
│  └──────────┘  └──────────┘  └──────────┘              │
│                                                          │
│  RECENT ORDERS                              [View all →] │
│  ──────────────────────────────────────────────────────  │
│  DH-2501-4821  Aashish S.    NPR 7,700  Dispatched  ··· │
│  DH-2501-4820  Priya T.      NPR 5,500  Packed       ··· │
│  DH-2501-4819  Bijay M.      NPR 2,200  Confirmed    ··· │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

Add the recent-orders table by creating an `LatestOrdersWidget` using `Filament\Widgets\TableWidget`.

---

## Orders Management

### OrderResource

```php
// app/Filament/Resources/OrderResource.php

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')->searchable()->sortable(),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn($s) => 'NPR ' . number_format($s)),
                BadgeColumn::make('status')
                    ->colors([
                        'warning'  => 'PENDING_PAYMENT',
                        'primary'  => 'CONFIRMED',
                        'secondary' => 'PACKED',
                        'info'     => 'DISPATCHED',
                        'success'  => 'DELIVERED',
                        'danger'   => 'CANCELLED',
                        'warning'  => 'EXCHANGE_REQUESTED',
                    ]),
                TextColumn::make('created_at')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatus::class),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('change_status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('status')->options(OrderStatus::class)->required(),
                        Textarea::make('note'),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->update(['status' => $data['status']]);
                        $record->statusHistory()->create([
                            'status' => $data['status'],
                            'note'   => $data['note'],
                            'changed_by' => auth()->user()->email,
                        ]);
                        // SMS the customer
                        app(SmsService::class)->sendStatusUpdate($record);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
```

### Order Detail View

```php
public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            Section::make('Customer')
                ->schema([
                    TextEntry::make('customer_name'),
                    TextEntry::make('address'),
                    TextEntry::make('city'),
                    TextEntry::make('delivery_note'),
                ]),
            Section::make('Payment')
                ->schema([
                    TextEntry::make('payment_method'),
                    TextEntry::make('payment_ref')->label('Transaction ref'),
                    TextEntry::make('total')
                        ->formatStateUsing(fn($s) => 'NPR ' . number_format($s)),
                ]),
            RepeatableEntry::make('items')
                ->schema([
                    TextEntry::make('product.name'),
                    TextEntry::make('variant.size'),
                    TextEntry::make('quantity'),
                    TextEntry::make('unit_price')
                        ->formatStateUsing(fn($s) => 'NPR ' . number_format($s)),
                ]),
        ]);
}
```

The packing slip is a plain Blade view at `resources/views/admin/packing-slip.blade.php`, opened via a Filament `Action` that redirects to `/admin/orders/{id}/packing-slip?print=1`.

---

## Products Management

### ProductResource

```php
// app/Filament/Resources/ProductResource.php

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Details')->schema([
                TextInput::make('name')->required(),
                TextInput::make('slug')->unique(ignoreRecord: true)->required(),
                TextInput::make('color_name')->required(),
                TextInput::make('price')->numeric()->prefix('NPR')->required(),
                TextInput::make('compare_at_price')->numeric()->prefix('NPR'),
                Select::make('category')->options(Category::class)->required(),
                Select::make('wardrobe_role')->options(WardrobeRole::class),
                Textarea::make('description')->rows(3),
                MarkdownEditor::make('fabric_story'),
                TagsInput::make('tags'),
                Toggle::make('is_active')->label('Visible on shop'),
            ]),
            Section::make('Images')->schema([
                // Spatie Media Library upload field
                SpatieMediaLibraryFileUpload::make('images')
                    ->multiple()
                    ->reorderable()
                    ->image()
                    ->collection('product-images'),
            ]),
            Section::make('SEO')->schema([
                TextInput::make('seo_title'),
                Textarea::make('seo_description')->rows(2),
            ]),
        ]);
    }
}
```

### ProductVariantResource (Relation Manager)

Variants are managed inline within the product form via a Filament Relation Manager:

```php
// app/Filament/Resources/ProductResource/RelationManagers/VariantsRelationManager.php

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('size'),
            TextColumn::make('sku'),
            TextColumn::make('stock'),
            TextColumn::make('reserved_stock'),
            TextColumn::make('available')
                ->getStateUsing(fn($r) => $r->stock - $r->reserved_stock),
        ])->headerActions([
            Tables\Actions\CreateAction::make(),
        ])->actions([
            Tables\Actions\EditAction::make(),
        ]);
    }
}
```

The "Alert restock subscribers" button is a Filament `Action` on the `ProductResource` table row that triggers `RestockNotificationJob`.

---

## Drop Management

A `DropResource` lists scheduled drops. The publish action:

```php
Action::make('publish')
    ->requiresConfirmation()
    ->action(function (Drop $record) {
        $record->products()->update(['is_active' => true]);
        // SMS send to subscribers via queued job
        dispatch(new DropAnnouncementJob($record));
        $record->update(['status' => 'PUBLISHED', 'published_at' => now()]);
    });
```

Scheduled drops are executed via a Laravel scheduled command (`app/Console/Commands/PublishScheduledDrops.php`) that runs every minute via `php artisan schedule:run`.

---

## Analytics

The analytics page is a custom Filament page that renders the Plausible shared embed in an iframe:

```php
// app/Filament/Pages/Analytics.php

class Analytics extends Page
{
    protected static string $view = 'filament.pages.analytics';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
}
```

```html
{{-- resources/views/filament/pages/analytics.blade.php --}}
<x-filament-panels::page>
    <iframe
        src="{{ config('services.plausible.embed_url') }}"
        scrolling="no"
        class="w-full"
        style="height: 1600px; border: none;"
    ></iframe>
</x-filament-panels::page>
```

---

## Admin Auth

Filament handles authentication entirely. No middleware to write:

```php
// config/filament.php (auto-configured)
// Login page: /admin/login
// Session: standard Laravel session (cookie-based)
// Guard: 'web' (Filament default)
```

To create an admin user:
```bash
php artisan make:filament-user
```

The `User` model uses Filament's `HasFilamentAdminPanel` trait. Password is stored as bcrypt in the `users` table — no Supabase dependency.

No two-factor auth in Phase 1. Added via `filament/two-factor-authentication` in Phase 3.
