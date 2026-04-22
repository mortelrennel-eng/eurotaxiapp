<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-d'));

        // Get monthly revenue data (last 6 months)
        $monthlyRevenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('M', strtotime("-$i months"));
            $startDate = date('Y-m-01', strtotime("-$i months"));
            $endDate = date('Y-m-t', strtotime("-$i months"));
            
            // Get boundary collections
            $boundary = DB::table('boundaries')
                ->whereNull('deleted_at')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('actual_boundary') ?? 0;
            
            // Get expenses
            $expenses = DB::table('expenses')
                ->whereNull('deleted_at')
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount') ?? 0;
            
            $net = $boundary - $expenses;
            
            $monthlyRevenueData[] = [
                'month' => $month,
                'boundary' => $boundary,
                'expenses' => $expenses,
                'net' => $net
            ];
        }

        // Get unit idle analysis
        $unitIdleAnalysis = DB::table('units as u')
            ->whereNull('u.deleted_at')
            ->leftJoin('maintenance as m', function($join) {
                $join->on('u.id', '=', 'm.unit_id')
                    ->whereNull('m.deleted_at');
            })
            ->selectRaw('
                u.plate_number as unit,
                COUNT(m.id) as breakdown_count,
                SUM(CASE WHEN m.status = "completed" THEN DATEDIFF(m.date_completed, m.date_started) ELSE 0 END) as total_maintenance_days
            ')
            ->where('m.date_started', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 30 DAY)'))
            ->groupBy('u.id', 'u.plate_number')
            ->get();

        // Get driver performance
        $driverPerformance = DB::table('boundaries as b')
            ->whereNull('b.deleted_at')
            ->join('drivers as d', 'b.driver_id', '=', 'd.id')
            ->whereNull('d.deleted_at')
            ->selectRaw('
                CONCAT(COALESCE(d.first_name,\'\'), \' \', COALESCE(d.last_name,\'\')) as full_name,
                COUNT(b.id) as days_worked,
                SUM(b.actual_boundary) as total_collected,
                AVG(b.actual_boundary) as avg_daily,
                SUM(b.excess) - SUM(b.shortage) as net_excess
            ')
            ->whereBetween('b.date', [$date_from, $date_to])
            ->groupBy('d.id', 'full_name')
            ->orderByDesc('avg_daily')
            ->limit(10)
            ->get();

        // Get expense trends
        $expenseTrends = DB::table('expenses')
            ->whereNull('deleted_at')
            ->selectRaw('
                DATE_FORMAT(date, "%Y-%m") as month,
                SUM(amount) as total,
                COUNT(*) as count
            ')
            ->whereBetween('date', [$date_from, $date_to])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get maintenance costs by type
        $maintenanceCosts = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->selectRaw('
                maintenance_type,
                SUM(cost) as total_cost,
                COUNT(*) as count,
                AVG(cost) as avg_cost
            ')
            ->whereBetween('date_started', [$date_from, $date_to])
            ->groupBy('maintenance_type')
            ->orderByDesc('total_cost')
            ->get();

        // Calculate total boundary and expenses
        $total_boundary = DB::table('boundaries')->whereNull('deleted_at')->count();
        $total_expenses = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->sum('amount') ?? 0;
        
        // Calculate net income and active drivers
        $net_income = 0; // Placeholder - would need revenue data to calculate
        $active_drivers = DB::table('drivers')
            ->whereNull('deleted_at')
            ->whereIn('driver_status', ['available', 'assigned'])
            ->count();
        
        // Get top performing units
        $top_units = DB::table('units')
            ->whereNull('deleted_at')
            ->leftJoin('drivers as d', 'units.driver_id', '=', 'd.id')
            ->select('units.*', DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"))
            ->addSelect(DB::raw('0 as total_collected, 0 as days_operated')) // Placeholder values
            ->orderBy('units.plate_number')
            ->limit(5)
            ->get();
        
        // Get daily trend data
        $daily_trend = DB::table('expenses')
            ->whereNull('deleted_at')
            ->selectRaw('DATE(date) as date, SUM(amount) as total')
            ->whereBetween('date', [$date_from, $date_to])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Get expense by category
        $expense_by_category = DB::table('expenses')
            ->whereNull('deleted_at')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->whereBetween('date', [$date_from, $date_to])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('analytics.index', compact(
            'monthlyRevenueData',
            'unitIdleAnalysis',
            'driverPerformance',
            'expenseTrends',
            'maintenanceCosts',
            'total_boundary',
            'total_expenses',
            'net_income',
            'active_drivers',
            'top_units',
            'daily_trend',
            'expense_by_category',
            'date_from',
            'date_to'
        ));
    }
}
