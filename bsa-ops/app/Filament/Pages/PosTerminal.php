<?php

namespace App\Filament\Pages;

use App\Enums\ProductCategory;
use App\Models\Member;
use App\Models\Product;
use App\Services\PosService;
use App\Support\Money;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PosTerminal extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'POS terminal';

    protected static string $view = 'filament.pages.pos-terminal';

    public string $category = 'kitchen';

    public string $search = '';

    /** @var array<string, int> product id => quantity */
    public array $cart = [];

    public string $memberSearch = '';

    public ?string $memberId = null;

    // ---- Catalog ----

    public function getProductsProperty(): Collection
    {
        $term = trim($this->search);

        return Product::query()
            ->active()
            ->where('category', $this->category)
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")))
            ->orderBy('name')
            ->get();
    }

    public function getCategoriesProperty(): array
    {
        return ProductCategory::cases();
    }

    // ---- Member ----

    public function getMemberResultsProperty(): Collection
    {
        $term = trim($this->memberSearch);

        if ($this->memberId || strlen($term) < 2) {
            return collect();
        }

        return Member::query()
            ->where(fn ($q) => $q
                ->where('phone', 'like', "%{$term}%")
                ->orWhere('member_code', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%"))
            ->limit(5)
            ->get();
    }

    public function getMemberProperty(): ?Member
    {
        return $this->memberId ? Member::find($this->memberId) : null;
    }

    public function attachMember(string $id): void
    {
        $this->memberId = $id;
        $this->memberSearch = '';
    }

    public function detachMember(): void
    {
        $this->memberId = null;
    }

    // ---- Cart ----

    public function addToCart(string $productId): void
    {
        $this->cart[$productId] = ($this->cart[$productId] ?? 0) + 1;
    }

    public function decrement(string $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]--;

        if ($this->cart[$productId] < 1) {
            unset($this->cart[$productId]);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function getCartItemsProperty(): Collection
    {
        if ($this->cart === []) {
            return collect();
        }

        $products = Product::query()->whereIn('id', array_keys($this->cart))->get()->keyBy('id');
        $member = $this->member;

        return collect($this->cart)
            ->map(function (int $quantity, string $id) use ($products, $member) {
                $product = $products[$id] ?? null;

                if (! $product) {
                    return null;
                }

                $unit = $product->priceFor($member);

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unit,
                    'line_total' => $unit * $quantity,
                    'member_discount' => ($product->price - $unit) * $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public function getCartTotalProperty(): int
    {
        return (int) $this->cartItems->sum('line_total');
    }

    public function getMemberSavingsProperty(): int
    {
        return (int) $this->cartItems->sum('member_discount');
    }

    // ---- Checkout ----

    public function checkout(string $method): void
    {
        if ($this->cart === []) {
            Notification::make()->warning()->title('The cart is empty')->send();

            return;
        }

        $lines = $this->cartItems
            ->map(fn (array $item) => ['product' => $item['product'], 'quantity' => $item['quantity']])
            ->all();

        try {
            $invoice = app(PosService::class)->sale(
                lines: $lines,
                member: $this->member,
                method: $method,
                cashier: auth()->user(),
            );
        } catch (ValidationException $e) {
            Notification::make()->danger()
                ->title('Sale failed')
                ->body(collect($e->errors())->flatten()->first())
                ->send();

            return;
        }

        Notification::make()->success()
            ->title("Sale complete — {$invoice->invoice_number}")
            ->body(Money::npr($invoice->total)
                . ($method === PosService::ON_ACCOUNT
                    ? ' put on ' . $this->member->full_name . "'s account"
                    : ' received'))
            ->send();

        $this->clearCart();
        $this->detachMember();
    }
}
