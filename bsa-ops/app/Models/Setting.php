<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn (Setting $setting) => Cache::forget("setting:{$setting->key}"));
        static::deleted(fn (Setting $setting) => Cache::forget("setting:{$setting->key}"));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(
            "setting:{$key}",
            fn () => static::where('key', $key)->value('value')
        ) ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }
}
