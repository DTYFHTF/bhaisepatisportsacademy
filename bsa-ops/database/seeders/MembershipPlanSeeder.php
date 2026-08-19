<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::pluck('id', 'code');

        // Prices in paisa (NPR × 100).
        $plans = [
            // Gym — time based
            ['code' => 'GYM-1M', 'name' => 'Gym Monthly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 1, 'price' => 350000, 'admission_fee' => 100000, 'freeze_allowance_days' => 0, 'departments' => ['GYM']],
            ['code' => 'GYM-3M', 'name' => 'Gym Quarterly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 3, 'price' => 900000, 'admission_fee' => 100000, 'freeze_allowance_days' => 7, 'departments' => ['GYM']],
            ['code' => 'GYM-6M', 'name' => 'Gym Half-yearly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 6, 'price' => 1600000, 'admission_fee' => 100000, 'freeze_allowance_days' => 14, 'departments' => ['GYM']],
            ['code' => 'GYM-12M', 'name' => 'Gym Annual', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 12, 'price' => 2800000, 'admission_fee' => 0, 'freeze_allowance_days' => 30, 'departments' => ['GYM']],
            ['code' => 'GYM-STU', 'name' => 'Gym Student Off-peak', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 1, 'price' => 250000, 'admission_fee' => 50000, 'is_off_peak' => true, 'off_peak_start' => '10:00', 'off_peak_end' => '16:00', 'max_age' => 25, 'departments' => ['GYM']],
            // Pool
            ['code' => 'POOL-1M', 'name' => 'Pool Monthly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 1, 'price' => 450000, 'admission_fee' => 50000, 'departments' => ['POOL']],
            ['code' => 'POOL-P10', 'name' => 'Pool 10-swim Pack', 'plan_type' => 'session_pack', 'session_count' => 10, 'validity_days' => 90, 'price' => 350000, 'departments' => ['POOL']],
            ['code' => 'POOL-P20', 'name' => 'Pool 20-swim Pack', 'plan_type' => 'session_pack', 'session_count' => 20, 'validity_days' => 150, 'price' => 600000, 'departments' => ['POOL']],
            // Sauna
            ['code' => 'SAUNA-P10', 'name' => 'Sauna 10-visit Pack', 'plan_type' => 'session_pack', 'session_count' => 10, 'validity_days' => 90, 'price' => 400000, 'departments' => ['SAUNA']],
            // Badminton
            ['code' => 'BADM-1M', 'name' => 'Badminton Monthly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 1, 'price' => 300000, 'admission_fee' => 50000, 'departments' => ['BADMINTON']],
            ['code' => 'BADM-P12', 'name' => 'Badminton 12-session Pack', 'plan_type' => 'session_pack', 'session_count' => 12, 'validity_days' => 60, 'price' => 250000, 'departments' => ['BADMINTON']],
            // Futsal
            ['code' => 'FUTSAL-P10', 'name' => 'Futsal 10-session Pack', 'plan_type' => 'session_pack', 'session_count' => 10, 'validity_days' => 60, 'price' => 300000, 'departments' => ['FUTSAL']],
            // Combos
            ['code' => 'ALL-1M', 'name' => 'All-Access Monthly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 1, 'price' => 800000, 'admission_fee' => 100000, 'freeze_allowance_days' => 0, 'departments' => ['GYM', 'POOL', 'SAUNA', 'BADMINTON', 'FUTSAL']],
            ['code' => 'ALL-3M', 'name' => 'All-Access Quarterly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 3, 'price' => 2100000, 'admission_fee' => 100000, 'freeze_allowance_days' => 10, 'departments' => ['GYM', 'POOL', 'SAUNA', 'BADMINTON', 'FUTSAL']],
            ['code' => 'ALL-12M', 'name' => 'All-Access Annual', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 12, 'price' => 7000000, 'admission_fee' => 0, 'freeze_allowance_days' => 45, 'departments' => ['GYM', 'POOL', 'SAUNA', 'BADMINTON', 'FUTSAL']],
            ['code' => 'GYMSAUNA-1M', 'name' => 'Gym + Sauna Monthly', 'plan_type' => 'time_based', 'interval_unit' => 'months', 'interval_count' => 1, 'price' => 600000, 'admission_fee' => 100000, 'departments' => ['GYM', 'SAUNA']],
        ];

        foreach ($plans as $i => $plan) {
            $departments = $plan['departments'];
            unset($plan['departments']);

            $model = MembershipPlan::updateOrCreate(
                ['code' => $plan['code']],
                [...$plan, 'sort_order' => $i + 1, 'is_active' => true],
            );

            $model->departments()->sync(
                collect($departments)->map(fn (string $code) => $dept[$code])->all()
            );
        }
    }
}
