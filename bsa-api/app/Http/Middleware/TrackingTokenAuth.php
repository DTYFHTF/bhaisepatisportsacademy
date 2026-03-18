<?php

namespace App\Http\Middleware;

use App\Models\TrackingToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackingTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->query('token');

        if (! $token) {
            return response()->json(['error' => 'UNAUTHORIZED', 'message' => 'Tracking token required.'], 401);
        }

        $trackingToken = TrackingToken::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $trackingToken) {
            return response()->json(['error' => 'UNAUTHORIZED', 'message' => 'Invalid or expired tracking token.'], 401);
        }

        $request->merge(['tracking_order_id' => $trackingToken->order_id]);

        return $next($request);
    }
}
