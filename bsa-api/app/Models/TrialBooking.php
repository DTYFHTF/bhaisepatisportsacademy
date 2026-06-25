<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TrialBooking extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status'         => BookingStatus::class,
            'duration'       => 'integer',
            'scheduled_date' => 'date',
        ];
    }

    public static function generateRef(): string
    {
        $count = self::count();
        $number = str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        return "TR-{$number}";
    }
}