<?php

namespace Database\Seeders;

use App\Enums\CheckInSource;
use App\Enums\DenialReason;
use App\Enums\SubscriptionStatus;
use App\Models\CheckIn;
use App\Models\MemberSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * ~2,500 historical check-ins over the last 90 days, weekday-morning
 * weighted, written directly for speed (the live paths go through
 * CheckInService).
 */
class CheckInSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260721);

        $desk = User::where('email', 'desk1@bsa.com')->first();

        $subscriptions = MemberSubscription::query()
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Expired])
            ->with(['member', 'plan.departments'])
            ->get()
            ->filter(fn (MemberSubscription $s) => $s->plan->departments->isNotEmpty());

        $rows = [];
        $now = now();

        foreach ($subscriptions as $sub) {
            // Visit frequency per member: 1-5 times a week.
            $weeklyVisits = mt_rand(1, 5);

            $windowStart = max($sub->starts_on->timestamp, today()->subDays(90)->timestamp);
            $windowEnd = min(($sub->ends_on ?? today())->timestamp, today()->timestamp);

            if ($windowStart >= $windowEnd) {
                continue;
            }

            $days = intdiv($windowEnd - $windowStart, 86400);
            $visits = (int) round($days / 7 * $weeklyVisits);

            for ($v = 0; $v < $visits; $v++) {
                $day = Carbon::createFromTimestamp($windowStart)->addDays(mt_rand(0, max(0, $days - 1)));

                // Weekday-morning weighting: 65% between 05:30-10:00.
                $hour = mt_rand(0, 99) < 65 ? mt_rand(5, 9) : mt_rand(10, 20);

                $dept = $sub->plan->departments[mt_rand(0, $sub->plan->departments->count() - 1)];

                $rows[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid7(),
                    'member_id' => $sub->member_id,
                    'department_id' => $dept->id,
                    'member_subscription_id' => $sub->id,
                    'checked_in_at' => $day->setTime($hour, mt_rand(0, 59), mt_rand(0, 59)),
                    'source' => mt_rand(0, 9) < 8 ? CheckInSource::FrontDesk->value : CheckInSource::Qr->value,
                    'was_allowed' => true,
                    'denial_reason' => null,
                    'session_consumed' => $sub->plan->isPack(),
                    'access_device_id' => null,
                    'checked_in_by' => $desk?->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // A few denials for realism.
        foreach ($subscriptions->random(min(12, $subscriptions->count())) as $sub) {
            $rows[] = [
                'id' => (string) \Illuminate\Support\Str::uuid7(),
                'member_id' => $sub->member_id,
                'department_id' => $sub->plan->departments->first()->id,
                'member_subscription_id' => null,
                'checked_in_at' => today()->subDays(mt_rand(0, 30))->setTime(mt_rand(6, 19), mt_rand(0, 59)),
                'source' => CheckInSource::FrontDesk->value,
                'was_allowed' => false,
                'denial_reason' => collect([
                    DenialReason::SubscriptionExpired,
                    DenialReason::OutstandingDues,
                    DenialReason::NoSessionsRemaining,
                ])->random()->value,
                'session_consumed' => false,
                'access_device_id' => null,
                'checked_in_by' => $desk?->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            CheckIn::insert($chunk);
        }

        $this->command->info('Check-ins: ' . count($rows));
    }
}
