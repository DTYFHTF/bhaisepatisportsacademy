<?php

namespace App\Http\Controllers;

use App\Models\RestockAlert;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestockController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone'      => ['required', 'string', 'regex:/^9[78]\d{8}$/'],
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'variant_id' => ['nullable', 'uuid', 'exists:product_variants,id'],
        ]);

        $phoneHash = app(OtpService::class)->hashPhone($request->phone);

        RestockAlert::firstOrCreate([
            'phone_hash' => $phoneHash,
            'variant_id' => $request->variant_id,
        ], [
            'product_id' => $request->product_id,
        ]);

        return response()->json(['message' => 'Restock alert registered.']);
    }
}
