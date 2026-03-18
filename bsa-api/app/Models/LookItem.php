<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LookItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $guarded = [];

    public function look(): BelongsTo
    {
        return $this->belongsTo(Look::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
