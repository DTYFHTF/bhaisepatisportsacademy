<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use App\Services\BillingService;
use App\Services\SubscriptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Drives the REAL services (subscribe/renew/applyPayment) with
 * Carbon::setTestNow() so invoices, receipts, sequences and renewal
 * chains are internally consistent across 14 months of history.
 */
class SubscriptionAndBillingSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260720);

        $subscriptions = app(SubscriptionService::class);
        $billing = app(BillingService::class);

        $accountant = User::where('email', 'accounts@bsa.com')->first();
        $desk = User::where('email', 'desk1@bsa.com')->first();

        $planPool = [
            // code => weight
            'GYM-1M' => 22, 'GYM-3M' => 12, 'GYM-6M' => 6, 'GYM-12M' => 4, 'GYM-STU' => 6,
            'POOL-1M' => 8, 'POOL-P10' => 8, 'POOL-P20' => 4, 'SAUNA-P10' => 4,
            'BADM-1M' => 7, 'BADM-P12' => 5, 'FUTSAL-P10' => 4,
            'ALL-1M' => 5, 'ALL-3M' => 3, 'GYMSAUNA-1M' => 2,
        ];

        $plans = MembershipPlan::with('departments')->get()->keyBy('code');
        $weighted = collect($planPool)->flatMap(fn ($w, $code) => array_fill(0, $w, $code))->values();

        $methodPool = collect([
            ...array_fill(0, 60, PaymentMethod::Cash),
            ...array_fill(0, 15, PaymentMethod::Esewa),
            ...array_fill(0, 10, PaymentMethod::Khalti),
            ...array_fill(0, 8, PaymentMethod::BankTransfer),
            ...array_fill(0, 4, PaymentMethod::Card),
            ...array_fill(0, 3, PaymentMethod::Cheque),
        ]);

        $bouncedSeeded = 0;

        // Anchor BEFORE any setTestNow: today() inside the loop is faked.
        $realToday = Carbon::today();

        foreach (Member::all() as $member) {
            $plan = $plans[$weighted[mt_rand(0, $weighted->count() - 1)]];

            // Minors: force an age-compatible plan.
            if ($member->age !== null && $member->age < 18) {
                $plan = $plans[collect(['POOL-P10', 'BADM-P12', 'FUTSAL-P10'])->random()];
            }

            $current = null;
            $cursor = $member->joined_on->copy();

            // Walk terms forward from join date to today, renewing ~82% of the time.
            while ($cursor->lte($realToday)) {
                Carbon::setTestNow($cursor->copy()->setTime(mt_rand(7, 18), mt_rand(0, 59)));

                try {
                    $current = $current === null
                        ? $subscriptions->subscribe($member, $plan, creator: $desk)
                        : $subscriptions->renew($current, creator: $desk);
                } catch (\Throwable) {
                    break; // age-gated plan etc. — skip this member
                }

                $invoice = $current->invoice()->first();

                // Payment behaviour: 82% pay in full, 8% partial, 10% unpaid.
                $roll = mt_rand(0, 99);

                if ($invoice && $roll < 90) {
                    $method = $methodPool[mt_rand(0, $methodPool->count() - 1)];
                    $amount = $roll < 82 ? $invoice->balance : intdiv($invoice->balance, 2);

                    $payment = $billing->applyPayment($invoice, [
                        'amount' => $amount,
                        'method' => $method,
                        'gateway_txn_id' => in_array($method, [PaymentMethod::Esewa, PaymentMethod::Khalti], true)
                            ? strtoupper($method->value) . '-' . mt_rand(100000000, 999999999)
                            : null,
                        'cheque_number' => $method === PaymentMethod::Cheque ? (string) mt_rand(100000, 999999) : null,
                        'cheque_bank' => $method === PaymentMethod::Cheque ? collect(['Nabil Bank', 'NIC Asia', 'Global IME'])->random() : null,
                        'cheque_date' => $method === PaymentMethod::Cheque ? today() : null,
                        'received_at' => now(),
                    ], receiver: $desk);

                    // Non-instant methods: verify most, bounce two cheques overall.
                    if ($payment->status->value === 'pending_verification') {
                        if ($method === PaymentMethod::Cheque && $bouncedSeeded < 2) {
                            $billing->bouncePayment($payment, $accountant);
                            $bouncedSeeded++;
                        } elseif (mt_rand(0, 9) < 9) {
                            $billing->verifyPayment($payment, $accountant);
                        }
                    }
                }

                $ends = $current->ends_on;

                if ($ends === null || $ends->gte($realToday)) {
                    break; // current term still running
                }

                // Renew ~90% of the time; otherwise the membership lapses here.
                if (mt_rand(0, 99) >= 90) {
                    break;
                }

                $cursor = $ends->copy()->addDays(mt_rand(1, 10));
            }
        }

        Carbon::setTestNow();

        // Roll up expired statuses + overdue invoices as the nightly jobs would.
        $this->command->call('ops:expire-subscriptions');
        $this->command->call('ops:mark-overdue-invoices');

        // Keep the blacklisted member blacklisted (roll-up may have flipped it).
        Member::whereNotNull('blacklist_reason')->update(['status' => MemberStatus::Blacklisted]);

        $this->command->info('Invoices: ' . \App\Models\Invoice::count()
            . ' | Payments: ' . \App\Models\Payment::count()
            . ' | Open balance: NPR ' . number_format(\App\Models\Invoice::where('status', '!=', InvoiceStatus::Void)->sum('balance') / 100));
    }
}
