<?php

namespace App\Models;

use App\Enums\InvoiceSource;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasUuids, LogsActivity;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'taxable_amount' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'paid_total' => 'integer',
            'balance' => 'integer',
            'status' => InvoiceStatus::class,
            'source' => InvoiceSource::class,
            'voided_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total', 'paid_total', 'balance', 'voided_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ---- Relationships ----

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(MemberSubscription::class, 'member_subscription_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    // ---- Scopes ----

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatus::Issued,
            InvoiceStatus::PartiallyPaid,
            InvoiceStatus::Overdue,
        ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::Overdue);
    }

    // ---- Helpers ----

    /**
     * Recompute money totals from items. Does not save.
     */
    public function recalculateTotals(): void
    {
        $items = $this->items;

        $this->subtotal = $items->sum(fn (InvoiceItem $i) => $i->unit_price * $i->quantity);
        $this->discount_total = $items->sum('discount_amount');
        $this->tax_total = $items->sum('tax_amount');
        $this->taxable_amount = $items->where('tax_rate', '>', 0)
            ->sum(fn (InvoiceItem $i) => $i->line_total - $i->tax_amount);
        $this->total = $items->sum('line_total');
        $this->balance = max(0, $this->total - $this->paid_total);
    }
}
