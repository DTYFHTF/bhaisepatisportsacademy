<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All stock mutations run through here: every change appends a signed
 * stock_movements row (the ledger of truth) and adjusts the
 * products.stock_on_hand cache in the same transaction. Outbound
 * movements use guarded decrements so a single shuttlecock can never
 * be sold or issued twice.
 */
class InventoryService
{
    public function __construct(
        private readonly NumberSequenceService $sequences,
    ) {
    }

    /**
     * Receive goods from a supplier.
     *
     * @param  array<int, array{product: Product, quantity: int, unit_cost: int}>  $lines
     */
    public function receivePurchase(
        ?Supplier $supplier,
        array $lines,
        ?CarbonInterface $purchaseDate = null,
        ?string $referenceNo = null,
        ?User $receiver = null,
        ?string $notes = null,
    ): Purchase {
        if ($lines === []) {
            throw ValidationException::withMessages(['lines' => 'A purchase needs at least one line.']);
        }

        return DB::transaction(function () use ($supplier, $lines, $purchaseDate, $referenceNo, $receiver, $notes) {
            $purchaseDate ??= today();

            $purchase = Purchase::create([
                'voucher_number' => $this->sequences->purchaseNumber(),
                'supplier_id' => $supplier?->id,
                'purchase_date' => $purchaseDate,
                'reference_no' => $referenceNo,
                'total' => 0,
                'notes' => $notes,
                'received_by' => $receiver?->id,
            ]);

            $total = 0;

            foreach ($lines as $line) {
                $product = $line['product'];
                $quantity = (int) $line['quantity'];
                $unitCost = (int) $line['unit_cost'];

                if ($quantity < 1) {
                    throw ValidationException::withMessages(['quantity' => 'Quantities must be at least 1.']);
                }

                $lineTotal = $quantity * $unitCost;
                $total += $lineTotal;

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                $this->record($product, $quantity, StockMovementType::Purchase, $purchase, $receiver, unitCost: $unitCost);

                // Keep the product's replacement cost current.
                $product->update(['cost_price' => $unitCost]);
            }

            $purchase->update(['total' => $total]);

            return $purchase->load('items');
        });
    }

    /**
     * Issue stock for internal use — a tube of shuttlecocks to the
     * badminton courts, chlorine to the pool. Valued at cost and
     * attributed to the department's cost center for the P&L.
     */
    public function consume(
        Product $product,
        int $quantity,
        ?Department $department = null,
        ?User $user = null,
        ?string $notes = null,
    ): StockMovement {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        return DB::transaction(function () use ($product, $quantity, $department, $user, $notes) {
            $this->takeStock($product, $quantity);

            return $this->record(
                $product, -$quantity, StockMovementType::Consumption,
                null, $user,
                department: $department ?? $product->department,
                unitCost: $product->cost_price,
                notes: $notes,
            );
        });
    }

    /**
     * Reconcile the counted quantity against the ledger (stocktake).
     */
    public function adjust(Product $product, int $countedQuantity, ?User $user = null, ?string $reason = null): ?StockMovement
    {
        if ($countedQuantity < 0) {
            throw ValidationException::withMessages(['quantity' => 'Counted quantity cannot be negative.']);
        }

        return DB::transaction(function () use ($product, $countedQuantity, $user, $reason) {
            $product = Product::query()->whereKey($product->id)->lockForUpdate()->first();
            $delta = $countedQuantity - $product->stock_on_hand;

            if ($delta === 0) {
                return null;
            }

            $product->update(['stock_on_hand' => $countedQuantity]);

            return $this->record(
                $product, $delta, StockMovementType::Adjustment,
                null, $user,
                unitCost: $product->cost_price,
                notes: $reason ?? 'Stocktake reconciliation',
            );
        });
    }

    /**
     * Deduct stock for a POS sale line. Called inside PosService's
     * transaction; non-tracked products (cooked kitchen dishes) skip it.
     */
    public function sellStock(Product $product, int $quantity, Invoice $invoice, ?User $user = null): void
    {
        if (! $product->track_stock) {
            return;
        }

        $this->takeStock($product, $quantity);

        $this->record(
            $product, -$quantity, StockMovementType::Sale,
            $invoice, $user,
            unitCost: $product->cost_price,
        );
    }

    /**
     * Guarded decrement — refuses to oversell.
     */
    private function takeStock(Product $product, int $quantity): void
    {
        $updated = Product::query()
            ->whereKey($product->id)
            ->where('stock_on_hand', '>=', $quantity)
            ->decrement('stock_on_hand', $quantity);

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'stock' => "Not enough stock of {$product->name}: {$product->fresh()->stock_on_hand} {$product->unit}(s) on hand.",
            ]);
        }
    }

    private function record(
        Product $product,
        int $signedQuantity,
        StockMovementType $type,
        ?Model $reference,
        ?User $user,
        ?Department $department = null,
        ?int $unitCost = null,
        ?string $notes = null,
    ): StockMovement {
        if ($signedQuantity > 0) {
            Product::query()->whereKey($product->id)->increment('stock_on_hand', $signedQuantity);
        }

        return StockMovement::create([
            'product_id' => $product->id,
            'quantity' => $signedQuantity,
            'type' => $type,
            'department_id' => $department?->id,
            'unit_cost' => $unitCost,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
            'created_by' => $user?->id,
            'occurred_at' => now(),
        ]);
    }
}
