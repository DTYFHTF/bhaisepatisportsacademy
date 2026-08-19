<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\InventoryService;
use App\Support\Money;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Carbon;

class ManagePurchases extends ManageRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Receive purchase')
                ->slideOver()
                // The whole receipt goes through InventoryService so the
                // ledger, stock cache and voucher stay consistent.
                ->using(function (array $data) {
                    $lines = collect($data['lines'])
                        ->map(fn (array $line) => [
                            'product' => Product::findOrFail($line['product_id']),
                            'quantity' => (int) $line['quantity'],
                            'unit_cost' => Money::toPaisa($line['unit_cost_rupees']),
                        ])
                        ->all();

                    $purchase = app(InventoryService::class)->receivePurchase(
                        supplier: $data['supplier_id'] ? Supplier::find($data['supplier_id']) : null,
                        lines: $lines,
                        purchaseDate: Carbon::parse($data['purchase_date']),
                        referenceNo: $data['reference_no'] ?? null,
                        receiver: auth()->user(),
                        notes: $data['notes'] ?? null,
                    );

                    Notification::make()->success()
                        ->title("Received {$purchase->voucher_number} — " . Money::npr($purchase->total))
                        ->send();

                    return $purchase;
                }),
        ];
    }
}
