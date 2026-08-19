<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccessDecision;
use App\Enums\CheckInSource;
use App\Enums\DeviceProtocol;
use App\Http\Controllers\Controller;
use App\Models\AccessDevice;
use App\Models\AccessEvent;
use App\Models\CheckIn;
use App\Models\DeviceCommand;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * ZKTeco PUSH / ADMS protocol endpoint (K40 Pro, M2F-LR Pro, SenseFace…).
 *
 * These devices speak plain HTTP with tab-separated bodies and identify
 * themselves only by serial number — there is no bearer token, so the
 * routes sit outside `auth:sanctum`. Security notes and hardening advice
 * live in docs/10-zkteco-integration.md.
 *
 * Flow:
 *   GET  /iclock/cdata?SN=…&options=all  → handshake, we hand back config
 *   POST /iclock/cdata?SN=…&table=ATTLOG → punches, we record check-ins
 *   GET  /iclock/getrequest?SN=…         → device polls for our commands
 *   POST /iclock/devicecmd?SN=…          → device reports command results
 */
class AdmsController extends Controller
{
    /**
     * Handshake. The device asks for its operating parameters on boot and
     * periodically after that; a well-formed reply is what makes it start
     * pushing. `Realtime=1` asks for punches as they happen.
     */
    public function handshake(Request $request): Response
    {
        $device = $this->device($request);
        $device->update(['last_seen_at' => now(), 'ip_address' => $request->ip()]);

        $stamp = (string) now()->timestamp;

        $body = implode("\r\n", [
            "GET OPTION FROM: {$device->device_uid}",
            'Stamp=' . $stamp,
            'OpStamp=' . $stamp,
            'ErrorDelay=30',
            'Delay=10',
            'TransTimes=00:00;14:00',
            'TransInterval=1',
            'TransFlag=1111000000',
            'TimeZone=5.75',      // Nepal, UTC+5:45
            'Realtime=1',
            'Encrypt=0',
        ]);

        return $this->text($body);
    }

    /**
     * Attendance / access punches. Body is one record per line:
     *   PIN \t YYYY-MM-DD HH:MM:SS \t status \t verify \t workcode …
     *
     * The door has already opened by the time we hear about it (the device
     * decided locally from its synced whitelist), so we record what
     * happened rather than authorising it.
     */
    public function push(Request $request): Response
    {
        $device = $this->device($request);
        $device->update(['last_seen_at' => now()]);

        $table = $request->query('table', 'ATTLOG');

        if ($table !== 'ATTLOG') {
            // OPERLOG / USERINFO / BIODATA uploads — acknowledged, not stored.
            return $this->text('OK');
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) $request->getContent()) ?: [];
        $stored = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $parts = explode("\t", $line);
            $pin = trim($parts[0] ?? '');
            $timestamp = trim($parts[1] ?? '');

            if ($pin === '' || $timestamp === '') {
                continue;
            }

            $occurredAt = $this->parseTimestamp($timestamp);
            $member = $this->memberByPin($pin);

            // Append-only audit row for every punch, resolved or not.
            $alreadyLogged = AccessEvent::query()
                ->where('access_device_id', $device->id)
                ->where('occurred_at', $occurredAt)
                ->where('credential_hint', 'PIN:' . $pin)
                ->exists();

            if ($alreadyLogged) {
                continue; // devices re-send batches after a network blip
            }

            AccessEvent::create([
                'access_device_id' => $device->id,
                'device_uid' => $device->device_uid,
                'credential_hint' => 'PIN:' . $pin,
                'member_id' => $member?->id,
                'department_id' => $device->department_id,
                'decision' => $member ? AccessDecision::Allowed : AccessDecision::Denied,
                'deny_reason' => $member ? null : \App\Enums\DenialReason::MemberNotFound,
                'occurred_at' => $occurredAt,
                'raw_payload' => ['line' => $line, 'table' => $table],
            ]);

            if ($member && $device->department_id) {
                CheckIn::create([
                    'member_id' => $member->id,
                    'department_id' => $device->department_id,
                    'checked_in_at' => $occurredAt,
                    'source' => CheckInSource::DoorController,
                    'was_allowed' => true,
                    'session_consumed' => false,
                    'access_device_id' => $device->id,
                ]);
            }

            $stored++;
        }

        return $this->text("OK: {$stored}");
    }

    /**
     * Command queue. The device polls this; we hand back at most one
     * command per poll in the form `C:<sequence>:<command>`.
     */
    public function getRequest(Request $request): Response
    {
        $device = $this->device($request);
        $device->update(['last_seen_at' => now()]);

        $command = DeviceCommand::query()
            ->where('access_device_id', $device->id)
            ->pending()
            ->orderBy('sequence')
            ->first();

        if (! $command) {
            return $this->text('OK');
        }

        $command->update(['status' => 'sent', 'sent_at' => now()]);

        return $this->text("C:{$command->sequence}:{$command->command}");
    }

    /**
     * Command acknowledgement: `ID=<sequence>&Return=<code>&CMD=<verb>`.
     * Return=0 means success.
     */
    public function deviceCmd(Request $request): Response
    {
        $device = $this->device($request);

        $payload = [];
        parse_str(str_replace("\n", '&', trim((string) $request->getContent())), $payload);

        $sequence = $payload['ID'] ?? null;
        $return = $payload['Return'] ?? null;

        if ($sequence !== null) {
            DeviceCommand::query()
                ->where('access_device_id', $device->id)
                ->where('sequence', (int) $sequence)
                ->update([
                    'status' => ((string) $return === '0') ? 'acked' : 'failed',
                    'device_return' => (string) $return,
                    'acked_at' => now(),
                ]);
        }

        return $this->text('OK');
    }

    // ---------------------------------------------------------------

    private function device(Request $request): AccessDevice
    {
        $serial = (string) $request->query('SN', '');

        abort_if($serial === '', 400, 'Missing SN');

        $device = AccessDevice::query()
            ->where('device_uid', $serial)
            ->where('protocol', DeviceProtocol::ZktecoAdms)
            ->first();

        // Unregistered serials are refused: a device must be added in the
        // admin panel (with its department) before it can talk to us.
        abort_unless($device, 403, 'Unknown device');
        abort_unless($device->is_active, 403, 'Device deactivated');

        return $device;
    }

    private function memberByPin(string $pin): ?Member
    {
        $numeric = (int) preg_replace('/\D/', '', $pin);

        if ($numeric <= 0) {
            return null;
        }

        // devicePin() is the numeric tail of member_code; match on the
        // zero-padded code rather than scanning every member.
        return Member::query()
            ->where('member_code', 'like', '%' . str_pad((string) $numeric, 5, '0', STR_PAD_LEFT))
            ->first();
    }

    private function parseTimestamp(string $raw): Carbon
    {
        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return now();
        }
    }

    /** ADMS devices expect plain text, not JSON. */
    private function text(string $body): Response
    {
        return response($body, 200)->header('Content-Type', 'text/plain; charset=utf-8');
    }
}
