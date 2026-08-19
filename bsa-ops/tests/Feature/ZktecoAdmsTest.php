<?php

namespace Tests\Feature;

use App\Enums\DeviceProtocol;
use App\Models\AccessDevice;
use App\Models\AccessEvent;
use App\Models\CheckIn;
use App\Models\Department;
use App\Models\DeviceCommand;
use App\Models\Member;
use App\Services\SubscriptionService;
use App\Services\ZktecoSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class ZktecoAdmsTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    private Department $gym;

    private AccessDevice $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();

        $this->gym = $this->makeDepartment();
        $this->device = AccessDevice::create([
            'name' => 'Gym M2F-LR Pro',
            'device_uid' => 'CJVN231260099',
            'type' => 'door_controller',
            'protocol' => DeviceProtocol::ZktecoAdms,
            'department_id' => $this->gym->id,
            'is_active' => true,
        ]);
    }

    public function test_handshake_returns_the_config_block_the_firmware_expects(): void
    {
        $response = $this->get('/iclock/cdata?SN=CJVN231260099&options=all&pushver=2.4.1');

        $response->assertOk();
        $body = $response->getContent();

        $this->assertStringContainsString('GET OPTION FROM: CJVN231260099', $body);
        $this->assertStringContainsString('Realtime=1', $body);
        $this->assertStringContainsString('TimeZone=5.75', $body); // Nepal
        $this->assertNotNull($this->device->fresh()->last_seen_at);
    }

    public function test_unregistered_serial_is_refused(): void
    {
        $this->get('/iclock/cdata?SN=NOT-OURS&options=all')->assertForbidden();
    }

    public function test_deactivated_device_is_refused(): void
    {
        $this->device->update(['is_active' => false]);

        $this->get('/iclock/cdata?SN=CJVN231260099&options=all')->assertForbidden();
    }

    public function test_missing_serial_is_rejected(): void
    {
        $this->get('/iclock/cdata?options=all')->assertStatus(400);
    }

    public function test_attendance_push_creates_a_check_in_for_a_known_member(): void
    {
        $member = $this->makeMember(); // BSA-00001 → PIN 1

        $response = $this->call(
            'POST',
            '/iclock/cdata?SN=CJVN231260099&table=ATTLOG&Stamp=9999',
            content: "1\t2026-08-18 06:32:11\t0\t1\t0\t0\t0",
        );

        $response->assertOk();
        $this->assertStringContainsString('OK', $response->getContent());

        $this->assertDatabaseHas('check_ins', [
            'member_id' => $member->id,
            'department_id' => $this->gym->id,
            'source' => 'door_controller',
            'was_allowed' => true,
        ]);
        $this->assertDatabaseHas('access_events', [
            'device_uid' => 'CJVN231260099',
            'decision' => 'allowed',
            'member_id' => $member->id,
        ]);

        $this->assertSame(
            '2026-08-18 06:32:11',
            CheckIn::first()->checked_in_at->format('Y-m-d H:i:s'),
        );
    }

    public function test_multiple_punches_in_one_body_are_all_recorded(): void
    {
        $a = $this->makeMember(); // PIN 1
        $b = $this->makeMember(); // PIN 2

        $this->call('POST', '/iclock/cdata?SN=CJVN231260099&table=ATTLOG', content: implode("\n", [
            "1\t2026-08-18 06:30:00\t0\t1",
            "2\t2026-08-18 06:31:00\t0\t15",
        ]))->assertOk();

        $this->assertSame(2, CheckIn::count());
        $this->assertSame(2, AccessEvent::count());
    }

    public function test_replayed_batches_are_deduplicated(): void
    {
        $this->makeMember();
        $body = "1\t2026-08-18 06:30:00\t0\t1";

        $this->call('POST', '/iclock/cdata?SN=CJVN231260099&table=ATTLOG', content: $body)->assertOk();
        $this->call('POST', '/iclock/cdata?SN=CJVN231260099&table=ATTLOG', content: $body)->assertOk();

        $this->assertSame(1, AccessEvent::count());
        $this->assertSame(1, CheckIn::count());
    }

    public function test_unknown_pin_is_logged_but_creates_no_check_in(): void
    {
        $this->call('POST', '/iclock/cdata?SN=CJVN231260099&table=ATTLOG', content: "9999\t2026-08-18 06:30:00\t0\t1")
            ->assertOk();

        $this->assertSame(0, CheckIn::count());
        $this->assertDatabaseHas('access_events', [
            'decision' => 'denied',
            'deny_reason' => 'member_not_found',
        ]);
    }

    public function test_non_attlog_tables_are_acknowledged_without_storing(): void
    {
        $this->call('POST', '/iclock/cdata?SN=CJVN231260099&table=OPERLOG', content: "OPLOG 1\t2\t3")
            ->assertOk();

        $this->assertSame(0, AccessEvent::count());
    }

    public function test_eligible_member_is_queued_for_enrolment_and_served_to_the_device(): void
    {
        $plan = $this->makeMonthlyPlan($this->gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);

        $queued = app(ZktecoSyncService::class)->syncDevice($this->device);
        $this->assertSame(1, $queued);

        $command = DeviceCommand::first();
        $this->assertSame('enrol', $command->kind);
        $this->assertStringContainsString('DATA UPDATE USERINFO PIN=' . $member->devicePin(), $command->command);

        // The device polls and receives it in ZKTeco's C:<seq>:<cmd> form.
        $response = $this->get('/iclock/getrequest?SN=CJVN231260099');
        $response->assertOk();
        $this->assertStringStartsWith("C:{$command->sequence}:DATA UPDATE USERINFO", $response->getContent());
        $this->assertSame('sent', $command->fresh()->status);
    }

    public function test_ineligible_member_is_queued_for_revocation(): void
    {
        $this->makeMember(); // no subscription → not eligible

        app(ZktecoSyncService::class)->syncDevice($this->device);

        $this->assertSame('revoke', DeviceCommand::first()->kind);
        $this->assertStringContainsString('DATA DELETE USERINFO', DeviceCommand::first()->command);
    }

    public function test_sync_is_idempotent_and_does_not_requeue_unchanged_members(): void
    {
        $this->makeMember();
        $sync = app(ZktecoSyncService::class);

        $this->assertSame(1, $sync->syncDevice($this->device));
        $this->assertSame(0, $sync->syncDevice($this->device)); // already revoked
        $this->assertSame(1, DeviceCommand::count());
    }

    public function test_expiring_a_membership_flips_the_member_to_revoked(): void
    {
        $plan = $this->makeMonthlyPlan($this->gym);
        $member = $this->makeMember();
        $sub = app(SubscriptionService::class)->subscribe($member, $plan);
        $sync = app(ZktecoSyncService::class);

        $sync->syncDevice($this->device);
        $this->assertSame('enrol', DeviceCommand::orderByDesc('sequence')->first()->kind);

        // Membership lapses; the nightly sweep runs.
        $sub->update(['starts_on' => today()->subDays(60), 'ends_on' => today()->subDay()]);
        $this->artisan('ops:expire-subscriptions')->assertSuccessful();
        $this->artisan('ops:sync-access-devices')->assertSuccessful();

        $latest = DeviceCommand::query()->where('member_id', $member->id)->orderByDesc('sequence')->first();
        $this->assertSame('revoke', $latest->kind);
    }

    public function test_empty_queue_returns_ok(): void
    {
        $this->get('/iclock/getrequest?SN=CJVN231260099')
            ->assertOk()
            ->assertSee('OK');
    }

    public function test_device_acknowledgement_closes_the_command(): void
    {
        $this->makeMember();
        app(ZktecoSyncService::class)->syncDevice($this->device);
        $command = DeviceCommand::first();

        $this->get('/iclock/getrequest?SN=CJVN231260099')->assertOk();

        $this->call('POST', '/iclock/devicecmd?SN=CJVN231260099', content: "ID={$command->sequence}&Return=0&CMD=DATA")
            ->assertOk();

        $command->refresh();
        $this->assertSame('acked', $command->status);
        $this->assertNotNull($command->acked_at);
    }

    public function test_failed_command_is_marked_failed(): void
    {
        $this->makeMember();
        app(ZktecoSyncService::class)->syncDevice($this->device);
        $command = DeviceCommand::first();

        $this->call('POST', '/iclock/devicecmd?SN=CJVN231260099', content: "ID={$command->sequence}&Return=-1&CMD=DATA")
            ->assertOk();

        $this->assertSame('failed', $command->fresh()->status);
    }

    public function test_native_devices_are_not_touched_by_the_zkteco_sync(): void
    {
        AccessDevice::create([
            'name' => 'Pool turnstile',
            'device_uid' => 'NATIVE-01',
            'type' => 'turnstile',
            'protocol' => DeviceProtocol::Native,
            'department_id' => $this->gym->id,
            'is_active' => true,
        ]);
        $this->makeMember();

        app(ZktecoSyncService::class)->syncAll();

        $this->assertSame(0, DeviceCommand::query()
            ->whereHas('device', fn ($q) => $q->where('device_uid', 'NATIVE-01'))
            ->count());
    }
}
