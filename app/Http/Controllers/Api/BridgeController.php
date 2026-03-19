<?php
// app/Http/Controllers/Api/BridgeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BridgeController extends Controller
{
    public function heartbeat(Request $request)
    {
        return response()->json(['ok' => true]);
    }

    public function logs(Request $request)
    {
        return response()->json(['ok' => true]);
    }

    public function pollCommands(Request $request, string $clientId)
    {
        return response()->noContent();
    }

    public function ackCommand(Request $request, string $clientId)
    {
        return response()->json(['ok' => true]);
    }
}
