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

    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) {
            return null;
        }

        // If it's already a full URL (from upload or external), return as-is
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        // Otherwise, it's a relative storage path — serve via the public disk URL
        return asset('storage/' . $value);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
