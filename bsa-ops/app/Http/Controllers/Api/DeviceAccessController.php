<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccessDecision;
use App\Enums\CheckInSource;
use App\Enums\CredentialStatus;
use App\Enums\DenialReason;
use App\Http\Controllers\Controller;
use App\Models\AccessCredential;
use App\Models\AccessDevice;
use App\Models\AccessEvent;
use App\Services\CheckInService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceAccessController extends Controller
{
    /**
     * The hardware presents a credential; we answer allow/deny in one
     * round-trip, log the raw event, and record the check-in when allowed.
     */
    public function verify(Request $request, CheckInService $checkIns): JsonResponse
    {
        $device = $this->device($request);

        $data = $request->validate([
            'credential_type' => ['required', 'string'],
            'identifier' => ['required', 'string', 'max:255'],
        ]);

        $hash = AccessCredential::hashIdentifier($data['identifier']);

        $credential = AccessCredential::query()
            ->where('identifier_hash', $hash)
            ->with('member')
            ->first();

        // Unknown or dead credential → deny fast, but always log.
        if (! $credential || $credential->status !== CredentialStatus::Active) {
            $reason = $credential ? DenialReason::CredentialRevoked : DenialReason::UnknownCredential;

            $this->logEvent($device, $hash, $credential, null, AccessDecision::Denied, $reason);

            return $this->decisionResponse(AccessDecision::Denied, $reason);
        }

        $department = $device->department;

        if (! $department) {
            $this->logEvent($device, $hash, $credential, $credential->member_id, AccessDecision::Denied, DenialReason::DepartmentNotCovered);

            return response()->json([
                'decision' => AccessDecision::Denied->value,
                'reason' => 'device_not_assigned_to_department',
            ], 422);
        }

        $checkIn = $checkIns->checkIn(
            $credential->member,
            $department,
            CheckInSource::DoorController,
            device: $device,
        );

        $decision = $checkIn->was_allowed ? AccessDecision::Allowed : AccessDecision::Denied;

        $this->logEvent($device, $hash, $credential, $credential->member_id, $decision, $checkIn->denial_reason);

        $member = $credential->member;

        return $this->decisionResponse($decision, $checkIn->denial_reason, [
            'member_code' => $member->member_code,
            'name' => $member->full_name,
            'photo_url' => $member->photo_url,
            'valid_until' => $checkIn->subscription?->ends_on?->toDateString(),
            'session_consumed' => $checkIn->session_consumed,
        ]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $device = $this->device($request);

        $device->update([
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
            'firmware' => $request->input('firmware', $device->firmware),
        ]);

        return response()->json([
            'ok' => true,
            'server_time' => now()->toIso8601String(),
            'device' => $device->name,
        ]);
    }

    /**
     * Offline back-fill: devices flush decisions they made while the
     * network was down. Idempotent per (device, occurred_at, credential).
     */
    public function events(Request $request): JsonResponse
    {
        $device = $this->device($request);

        $data = $request->validate([
            'events' => ['required', 'array', 'max:500'],
            'events.*.identifier' => ['required', 'string'],
            'events.*.decision' => ['required', 'in:allowed,denied'],
            'events.*.occurred_at' => ['required', 'date'],
            'events.*.reason' => ['nullable', 'string'],
        ]);

        $stored = 0;

        foreach ($data['events'] as $event) {
            $hash = AccessCredential::hashIdentifier($event['identifier']);
            $occurredAt = \Illuminate\Support\Carbon::parse($event['occurred_at']);

            $exists = AccessEvent::query()
                ->where('access_device_id', $device->id)
                ->where('occurred_at', $occurredAt)
                ->where('credential_hint', $hash)
                ->exists();

            if ($exists) {
                continue;
            }

            $credential = AccessCredential::where('identifier_hash', $hash)->first();

            AccessEvent::create([
                'access_device_id' => $device->id,
                'device_uid' => $device->device_uid,
                'credential_hint' => $hash,
                'access_credential_id' => $credential?->id,
                'member_id' => $credential?->member_id,
                'department_id' => $device->department_id,
                'decision' => $event['decision'],
                'deny_reason' => $event['reason'] ?? null,
                'occurred_at' => $occurredAt,
                'raw_payload' => $event,
            ]);

            $stored++;
        }

        return response()->json(['ok' => true, 'stored' => $stored]);
    }

    private function device(Request $request): AccessDevice
    {
        $device = $request->user();

        abort_unless($device instanceof AccessDevice, 403, 'This endpoint is for devices only.');
        abort_unless($device->tokenCan('device:verify'), 403, 'Token lacks the device:verify ability.');
        abort_unless($device->is_active, 403, 'Device is deactivated.');

        return $device;
    }

    private function logEvent(
        AccessDevice $device,
        string $hash,
        ?AccessCredential $credential,
        ?string $memberId,
        AccessDecision $decision,
        ?DenialReason $reason,
    ): void {
        AccessEvent::create([
            'access_device_id' => $device->id,
            'device_uid' => $device->device_uid,
            'credential_hint' => $hash,
            'access_credential_id' => $credential?->id,
            'member_id' => $memberId,
            'department_id' => $device->department_id,
            'decision' => $decision,
            'deny_reason' => $reason,
            'occurred_at' => now(),
        ]);
    }

    private function decisionResponse(AccessDecision $decision, ?DenialReason $reason, ?array $member = null): JsonResponse
    {
        return response()->json([
            'decision' => $decision->value,
            'reason' => $reason?->value,
            'member' => $member,
        ]);
    }
}
