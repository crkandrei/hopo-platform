<?php

namespace App\Http\Controllers;

use App\Models\BridgeCommand;
use App\Models\Location;
use App\Models\LocationBridge;
use Illuminate\Http\Request;

class LocationBridgeController extends Controller
{
    public function generateKey(Request $request, Location $location)
    {
        $this->authorize('update', $location);

        $newKey = bin2hex(random_bytes(32));

        LocationBridge::updateOrCreate(
            ['location_id' => $location->id],
            ['api_key' => $newKey]
        );

        if ($request->expectsJson()) {
            return response()->json(['api_key' => $newKey]);
        }

        return redirect()->back()->with('success', 'API Key generat cu succes.');
    }

    public function createCommand(Request $request, Location $location)
    {
        $this->authorize('update', $location);

        $validated = $request->validate([
            'command' => 'required|in:restart,set_config',
            'payload' => 'nullable|array',
        ]);

        BridgeCommand::create([
            'location_id' => $location->id,
            'command'     => $validated['command'],
            'payload'     => $validated['payload'] ?? null,
            'status'      => 'pending',
        ]);

        return redirect()->back()->with('success', 'Comandă trimisă cu succes.');
    }
}
