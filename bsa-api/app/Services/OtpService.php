<?php

namespace App\Services;

use App\Exceptions\InvalidOtpException;
use App\Models\OtpCode;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class OtpService
{
    public function send(string $phone): array
    {
        $phoneHash = $this->hashPhone($phone);

        $recentCount = OtpCode::where('phone_hash', $phoneHash)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentCount >= 5) {
            throw new ThrottleRequestsException('Too many OTP requests. Try again later.');
        }

        $otp = random_int(100000, 999999);
        $isSmsConfigured = ! empty(config('services.sparrow.token'));

        OtpCode::create([
            'phone_hash' => $phoneHash,
            'code_hash'  => $this->hashOtp($otp, $phone),
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($isSmsConfigured) {
            app(SmsService::class)->send(
                $phone,
                "Your BSA verification code: {$otp}. Valid for 10 minutes."
            );
            return ['dev_otp' => null];
        }

        // Dev mode: no SMS configured - log and return the code so it can be shown in the UI
        Log::info("[DEV] OTP for {$phone}: {$otp}");

        return ['dev_otp' => (string) $otp];
    }

    public function verify(string $phone, string $otp): string
    {
        $phoneHash = $this->hashPhone($phone);

        $record = OtpCode::where('phone_hash', $phoneHash)
            ->where('code_hash', $this->hashOtp((int) $otp, $phone))
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (! $record) {
            throw new InvalidOtpException();
        }

        $record->update(['used_at' => now()]);

        $user = \App\Models\User::firstOrCreate(
            ['email' => "{$phoneHash}@phone.local"],
            ['name' => 'Customer', 'password' => bcrypt(\Illuminate\Support\Str::random(32))],
        );

        $token = $user->createToken('checkout', ['checkout'], now()->addHours(2));

        return $token->plainTextToken;
    }

    public function hashPhone(string $phone): string
    {
        return hash_hmac('sha256', $phone, config('app.otp_secret'));
    }

    private function hashOtp(int $otp, string $phone): string
    {
        return hash_hmac('sha256', "{$otp}:{$phone}", config('app.otp_secret'));
    }
}
