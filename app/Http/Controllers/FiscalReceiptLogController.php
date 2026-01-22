<?php

namespace App\Http\Controllers;

use App\Models\FiscalReceiptLog;
use App\Support\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FiscalReceiptLogController extends Controller
{
    /**
     * Display the fiscal receipt logs page
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces permis doar pentru super admin');
        }
        
        return view('fiscal-receipt-logs.index');
    }

    /**
     * Get fiscal receipt logs data (server-side)
     */
    public function data(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            return ApiResponder::error('Acces permis doar pentru super admin', 403);
        }

        // Inputs
        $page = max(1, (int) $request->input('page', 1));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }
        $search = trim((string) $request->input('search', ''));
        $statusFilter = $request->input('status', ''); // 'success', 'error', or ''
        $sortBy = (string) $request->input('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Build query - include both session receipts and Z reports
        $query = FiscalReceiptLog::with(['playSession.location', 'location']);

        // Search filter
        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%")
                  ->orWhereHas('location', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('playSession', function($q) use ($search) {
                      $q->whereHas('location', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                  });
            });
        }

        // Status filter
        if ($statusFilter !== '' && in_array($statusFilter, ['success', 'error'], true)) {
            $query->where('status', $statusFilter);
        }

        // Sorting
        $allowedSortColumns = ['created_at', 'status', 'filename'];
        if (!in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $total = $query->count();
        $logs = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Format data
        $rows = $logs->map(function($log) {
            // For Z reports, get location from direct relationship
            // For session receipts, get location from playSession
            $locationName = null;
            if ($log->type === 'z_report') {
                $locationName = $log->location->name ?? 'N/A';
            } else {
                $locationName = $log->playSession->location->name ?? 'N/A';
            }

            return [
                'id' => $log->id,
                'type' => $log->type,
                'play_session_id' => $log->play_session_id,
                'filename' => $log->filename,
                'status' => $log->status,
                'error_message' => $log->error_message,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $log->created_at->format('d.m.Y H:i'),
                'location_name' => $locationName,
            ];
        });

        return ApiResponder::success([
            'data' => $rows,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / max(1, $perPage)),
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'search' => $search,
            ],
        ]);
    }
}
