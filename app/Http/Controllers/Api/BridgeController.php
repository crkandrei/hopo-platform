<?php
// app/Http/Controllers/Api/BridgeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BridgeCommand;
use App\Models\BridgeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class BridgeController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
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

    public function logs(Request $request): JsonResponse
    {
        $bridge = $request->attributes->get('bridge');

        $data = $request->validate([
            'clientId'         => 'required|string',
            'logs'             => 'required|array|min:1',
            'logs.*.level'     => 'required|in:info,warn,error',
            'logs.*.message'   => 'required|string',
            'logs.*.timestamp' => 'required|date',
        ]);

        $now  = now();
        $rows = array_map(fn($log) => [
            'location_id' => $bridge->location_id,
            'level'       => $log['level'],
            'message'     => $log['message'],
            'timestamp'   => Carbon::parse($log['timestamp'])->toDateTimeString(),
            'created_at'  => $now,
        ], $data['logs']);

        BridgeLog::insert($rows);

        return response()->json(['ok' => true]);
    }

    public function pollCommands(Request $request, string $clientId): Response|JsonResponse
    {
        $bridge = $request->attributes->get('bridge');

        if ($bridge->client_id !== null && $bridge->client_id !== $clientId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $command = BridgeCommand::where('location_id', $bridge->location_id)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->first();

        if (!$command) {
            return response()->noContent();
        }

        $command->update(['status' => 'sent']);

        return response()->json([
            'commandId' => $command->id,
            'command'   => $command->command,
            'payload'   => $command->payload,
        ]);
    }

    public function ackCommand(Request $request, string $clientId): JsonResponse
    {
        $bridge = $request->attributes->get('bridge');

        if ($bridge->client_id !== null && $bridge->client_id !== $clientId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'commandId' => 'required|string',
            'success'   => 'required|boolean',
            'message'   => 'nullable|string',
        ]);

        $command = BridgeCommand::where('id', $data['commandId'])
            ->where('location_id', $bridge->location_id)
            ->first();

        if (!$command) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $command->update([
            'status'      => $data['success'] ? 'completed' : 'failed',
            'ack_message' => $data['message'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }
}
