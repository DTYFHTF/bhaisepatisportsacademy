<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function send(SendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->send($request->validated('phone'));

        $response = ['message' => 'OTP sent.'];

        // Include dev OTP hint only in non-production
        if (! empty($result['dev_otp']) && ! app()->isProduction()) {
            $response['dev_otp'] = $result['dev_otp'];
        }

        return response()->json($response);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^9[78]\d{8}$/'],
            'code'  => ['required', 'string', 'size:6'],
        ]);

        $token = $this->otpService->verify($request->phone, $request->code);

        return response()->json(['token' => $token]);
    }
}
