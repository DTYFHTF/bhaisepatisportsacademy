<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteStat extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
