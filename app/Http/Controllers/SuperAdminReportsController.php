<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Support\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuperAdminReportsController extends Controller
{
    /**
     * Display the superadmin reports page
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Acces permis doar pentru super admin');
        }
        
        return view('superadmin-reports.index');
    }

    /**
     * Get children with session counts data (server-side)
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
        $sortBy = (string) $request->input('sort_by', 'sessions_count');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // Build query - get all children with session count across all locations
        $query = Child::select([
                'children.id',
                'children.name',
                'children.location_id',
                DB::raw('COUNT(play_sessions.id) as sessions_count')
            ])
            ->leftJoin('play_sessions', 'children.id', '=', 'play_sessions.child_id')
            ->with('location')
            ->groupBy('children.id', 'children.name', 'children.location_id');

        // Search filter
        if ($search !== '') {
            $query->where('children.name', 'like', "%{$search}%");
        }

        // Count total before pagination (we need to wrap this)
        $countQuery = Child::select('children.id')
            ->leftJoin('play_sessions', 'children.id', '=', 'play_sessions.child_id')
            ->groupBy('children.id');
        
        if ($search !== '') {
            $countQuery->where('children.name', 'like', "%{$search}%");
        }
        
        $total = $countQuery->get()->count();

        // Sorting
        $allowedSortColumns = ['name', 'sessions_count'];
        if (!in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'sessions_count';
        }
        
        if ($sortBy === 'name') {
            $query->orderBy('children.name', $sortDir);
        } else {
            $query->orderBy('sessions_count', $sortDir);
        }

        // Pagination
        $children = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Format data
        $rows = $children->map(function($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'sessions_count' => (int) $child->sessions_count,
                'location_name' => $child->location->name ?? 'N/A',
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


