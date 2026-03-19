<?php
// app/Http/Controllers/Api/BridgeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BridgeController extends Controller
{
    public function heartbeat(Request $request): \Illuminate\Http\JsonResponse
    {
        $bridge = $request->attributes->get('bridge');

        $data = $request->validate([
            'clientId'     => 'required|string',
            'status'       => 'sometimes|string',
            'version'      => 'sometimes|nullable|string',
            'uptime'       => 'sometimes|nullable|integer',
            'bridgeMode'   => 'sometimes|nullable|string|in:live,test',
            'lastPrintAt'  => 'sometimes|nullable|date',
            'printCount'   => 'sometimes|nullable|integer',
            'zReportCount' => 'sometimes|nullable|integer',
            'errorCount'   => 'sometimes|nullable|integer',
        ]);

        $updateData = [
            'status'       => 'online',
            'last_seen_at' => now(),
        ];

        if (isset($data['version']))      $updateData['version']        = $data['version'];
        if (isset($data['uptime']))       $updateData['uptime']         = $data['uptime'];
        if (isset($data['bridgeMode']))   $updateData['mode']           = $data['bridgeMode'];
        if (isset($data['lastPrintAt']))  $updateData['last_print_at']  = $data['lastPrintAt'];
        if (isset($data['printCount']))   $updateData['print_count']    = $data['printCount'];
        if (isset($data['zReportCount'])) $updateData['z_report_count'] = $data['zReportCount'];
        if (isset($data['errorCount']))   $updateData['error_count']    = $data['errorCount'];

        // First heartbeat: set client_id
        if (is_null($bridge->client_id)) {
            $updateData['client_id'] = $data['clientId'];
        }

        $bridge->update($updateData);

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
