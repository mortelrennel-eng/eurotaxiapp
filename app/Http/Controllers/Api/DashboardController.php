<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;

class DashboardController extends Controller
{
    /**
     * Get summary statistics for the mobile dashboard.
     */
    public function index()
    {
        $stats = [];
        
        // Total units
        $stats['active_units'] = Unit::count();

        // Units under coding
        $stats['coding_units'] = DB::table('units')->whereRaw('LOWER(status) = ?', ['coding'])->count();

        // Units under maintenance
        $stats['maintenance_units'] = DB::table('units')->whereRaw('LOWER(status) = ?', ['maintenance'])->count();

        // Today's boundary collected
        $stats['today_boundary'] = DB::table('boundaries')
            ->whereDate('date', now()->toDateString())
            ->sum('boundary_amount') ?? 0;

        // Today's expenses
        $stats['today_expenses'] = DB::table('expenses')
            ->whereDate('date', now()->toDateString())
            ->sum('amount') ?? 0;

        // Net income today
        $stats['net_income'] = $stats['today_boundary'] - $stats['today_expenses'];

        // Active drivers
        $stats['active_drivers'] = DB::table('drivers as d')
            ->join('users as u', 'd.user_id', '=', 'u.id')
            ->where('u.is_active', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
