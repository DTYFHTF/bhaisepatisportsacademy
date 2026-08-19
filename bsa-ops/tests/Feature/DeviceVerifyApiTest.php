<?php

namespace Tests\Feature;

use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Models\AccessCredential;
use App\Models\AccessDevice;
use App\Models\Department;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class DeviceVerifyApiTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    private Department $gym;

    private AccessDevice $device;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();

        $this->gym = $this->makeDepartment();
        $this->device = AccessDevice::create([
            'name' => 'Gym door',
            'device_uid' => 'TEST-DOOR-01',
            'type' => 'door_controller',
            'department_id' => $this->gym->id,
            'is_active' => true,
        ]);
        $this->token = $this->device->createToken('device', ['device:verify'])->plainTextToken;
    }

    private function issueCard(\App\Models\Member $member, string $raw): AccessCredential
    {
        return AccessCredential::create([
            'member_id' => $member->id,
            'type' => CredentialType::RfidCard,
            'identifier_hash' => AccessCredential::hashIdentifier($raw),
            'identifier_hint' => substr($raw, -4),
            'status' => CredentialStatus::Active,
            'issued_at' => now(),
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->postJson('/api/v1/device/verify', ['credential_type' => 'rfid_card', 'identifier' => 'X'])
            ->assertUnauthorized();
    }

    public function test_token_without_ability_is_rejected(): void
    {
        $token = $this->device->createToken('bad', ['other:thing'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/device/verify', ['credential_type' => 'rfid_card', 'identifier' => 'X'])
            ->assertForbidden();
    }

    public function test_unknown_credential_is_denied_and_logged(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/v1/device/verify', ['credential_type' => 'rfid_card', 'identifier' => 'NOPE'])
            ->assertOk()
            ->assertJson(['decision' => 'denied', 'reason' => 'unknown_credential']);

        $this->assertDatabaseHas('access_events', [
            'device_uid' => 'TEST-DOOR-01',
            'deny_reason' => 'unknown_credential',
        ]);
    }

    public function test_revoked_credential_is_denied(): void
    {
        $member = $this->makeMember();
        $card = $this->issueCard($member, 'CARD-R');
        $card->update(['status' => CredentialStatus::Revoked]);

        $this->withToken($this->token)
            ->postJson('/api/v1/device/verify', ['credential_type' => 'rfid_card', 'identifier' => 'CARD-R'])
            ->assertOk()
            ->assertJson(['decision' => 'denied', 'reason' => 'credential_revoked']);
    }

    public function test_allowed_member_gets_member_payload_and_check_in_recorded(): void
    {
        $plan = $this->makeMonthlyPlan($this->gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);
        $this->issueCard($member, 'CARD-OK');

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/device/verify', ['credential_type' => 'rfid_card', 'identifier' => 'CARD-OK'])
            ->assertOk()
            ->assertJson(['decision' => 'allowed'])
            ->assertJsonPath('member.member_code', $member->member_code);

        $this->assertNotNull($response->json('member.valid_until'));

        $this->assertDatabaseHas('check_ins', [
            'member_id' => $member->id,
            'department_id' => $this->gym->id,
            'source' => 'door_controller',
            'was_allowed' => true,
        ]);
        $this->assertDatabaseHas('access_events', ['decision' => 'allowed', 'member_id' => $member->id]);
    }

    public function test_member_without_subscription_is_denied_at_the_door(): void
    {
        $member = $this->makeMember();
        $this->issueCard($member, 'CARD-NS');

        $this->withToken($this->token)
            ->postJson('/api/v1/device/verify', ['credential_type' => 'rfid_card', 'identifier' => 'CARD-NS'])
            ->assertOk()
            ->assertJson(['decision' => 'denied', 'reason' => 'no_active_subscription']);
    }

    public function test_heartbeat_updates_last_seen(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/v1/device/heartbeat', ['firmware' => 'v2.1'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertNotNull($this->device->fresh()->last_seen_at);
        $this->assertSame('v2.1', $this->device->fresh()->firmware);
    }

    public function test_offline_event_batch_is_stored_idempotently(): void
    {
        $payload = ['events' => [[
            'identifier' => 'CARD-OFF',
            'decision' => 'denied',
            'occurred_at' => now()->subHour()->toIso8601String(),
            'reason' => 'no_active_subscription',
        ]]];

        $this->withToken($this->token)->postJson('/api/v1/device/events', $payload)
            ->assertOk()->assertJson(['stored' => 1]);

        // Same batch replayed → deduped.
        $this->withToken($this->token)->postJson('/api/v1/device/events', $payload)
            ->assertOk()->assertJson(['stored' => 0]);

        $this->assertSame(1, \App\Models\AccessEvent::count());
    }

    public function test_inactive_device_is_refused(): void
    {
        $this->device->update(['is_active' => false]);

        $this->withToken($this->token)
            ->postJson('/api/v1/device/verify', ['credential_type' => 'rfid_card', 'identifier' => 'X'])
            ->assertForbidden();
    }
}
