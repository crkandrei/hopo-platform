<?php
// app/Http/Middleware/BridgeApiAuth.php

namespace App\Http\Middleware;

use App\Models\LocationBridge;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BridgeApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $key = substr($header, 7);

        if (empty($key)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $bridge = LocationBridge::where('api_key', $key)->with('location')->first();

        if (!$bridge) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->attributes->set('bridge', $bridge);
        $request->attributes->set('bridgeLocation', $bridge->location);

        return $next($request);
    }
}
