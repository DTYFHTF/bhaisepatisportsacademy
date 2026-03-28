<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features'         => 'array',
            'price'            => 'integer',
            'sessions_per_week'=> 'integer',
            'sort_order'       => 'integer',
            'is_popular'       => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
