<?php

namespace App\Services;

use App\Enums\DeviceProtocol;
use App\Models\AccessDevice;
use App\Models\DeviceCommand;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

/**
 * Keeps ZKTeco ADMS doors in step with our eligibility engine.
 *
 * ZKTeco hardware decides access **locally** from the user list stored on
 * the device — it never asks us at the moment somebody presents a face or
 * finger. So instead of answering per swipe (as our native protocol does),
 * we push the *whitelist*: enrol members who are currently eligible and
 * revoke the ones who are not. See docs/10-zkteco-integration.md.
 */
class ZktecoSyncService
{
    public function __construct(
        private readonly EligibilityService $eligibility,
    ) {
    }

    /**
     * Re-evaluate one member against every ADMS door and queue the
     * resulting enrol/revoke commands.
     *
     * @return int number of commands queued
     */
    public function syncMember(Member $member): int
    {
        $queued = 0;

        foreach ($this->devices() as $device) {
            $allowed = $device->department
                && $this->eligibility->check($member, $device->department)->allowed;

            $queued += $this->reconcile($device, $member, $allowed) ? 1 : 0;
        }

        return $queued;
    }

    /**
     * Re-evaluate every member against one device (used after adding a
     * device, or as the nightly safety net).
     */
    public function syncDevice(AccessDevice $device): int
    {
        if (! $device->department) {
            return 0;
        }

        $queued = 0;

        Member::query()->with('subscriptions.plan.departments')->chunkById(200, function ($members) use ($device, &$queued) {
            foreach ($members as $member) {
                $allowed = $this->eligibility->check($member, $device->department)->allowed;
                $queued += $this->reconcile($device, $member, $allowed) ? 1 : 0;
            }
        });

        return $queued;
    }

    public function syncAll(): int
    {
        $queued = 0;

        foreach ($this->devices() as $device) {
            $queued += $this->syncDevice($device);
        }

        return $queued;
    }

    /**
     * Queue a command only when it would change the device's state —
     * we track the last enrol/revoke we sent per member per device so
     * nightly runs don't flood the queue with no-ops.
     */
    private function reconcile(AccessDevice $device, Member $member, bool $allowed): bool
    {
        // Ordered by sequence, not created_at: the sequence is strictly
        // monotonic per device, whereas two commands issued in the same
        // second tie on timestamp and could report the wrong current
        // state — which would leave a lapsed member enrolled at the door.
        $lastKind = DeviceCommand::query()
            ->where('access_device_id', $device->id)
            ->where('member_id', $member->id)
            ->whereIn('kind', ['enrol', 'revoke'])
            ->orderByDesc('sequence')
            ->value('kind');

        $wantedKind = $allowed ? 'enrol' : 'revoke';

        if ($lastKind === $wantedKind) {
            return false;
        }

        $this->queue(
            $device,
            $allowed ? $this->enrolCommand($member) : $this->revokeCommand($member),
            $wantedKind,
            $member,
        );

        return true;
    }

    /**
     * `DATA UPDATE USERINFO` — creates or updates the user on the device.
     * Fields are tab-separated per the PUSH SDK spec.
     */
    private function enrolCommand(Member $member): string
    {
        return implode("\t", [
            'DATA UPDATE USERINFO PIN=' . $member->devicePin(),
            'Name=' . mb_substr($member->full_name, 0, 24),
            'Pri=0',              // ordinary user, not an admin
            'Passwd=',
            'Card=',
            'Grp=1',
            'TZ=0000000000000000', // no per-user time zone restriction
        ]);
    }

    private function revokeCommand(Member $member): string
    {
        return 'DATA DELETE USERINFO PIN=' . $member->devicePin();
    }

    public function queue(AccessDevice $device, string $command, string $kind, ?Member $member = null): DeviceCommand
    {
        return DB::transaction(function () use ($device, $command, $kind, $member) {
            $next = (int) DeviceCommand::query()
                ->where('access_device_id', $device->id)
                ->lockForUpdate()
                ->max('sequence') + 1;

            return DeviceCommand::create([
                'access_device_id' => $device->id,
                'sequence' => $next,
                'command' => $command,
                'kind' => $kind,
                'member_id' => $member?->id,
                'status' => 'pending',
            ]);
        });
    }

    /** @return \Illuminate\Support\Collection<int, AccessDevice> */
    private function devices()
    {
        return AccessDevice::query()
            ->where('protocol', DeviceProtocol::ZktecoAdms)
            ->where('is_active', true)
            ->with('department')
            ->get();
    }
}
