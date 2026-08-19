<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use HasUuids, LogsActivity;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'expense_date' => 'date',
            'payment_method' => PaymentMethod::class,
            'status' => ExpenseStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ExpenseStatus::Approved);
    }

    /**
     * Shared overhead = no department; allocated pro-rata in P&L.
     */
    public function scopeOverhead(Builder $query): Builder
    {
        return $query->whereNull('department_id');
    }
}
