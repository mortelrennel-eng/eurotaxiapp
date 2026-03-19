<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Unit;
use App\Models\Boundary;
use App\Models\Maintenance;
use App\Models\Expense;
use App\Models\User;
use App\Models\SystemAlert;
use App\Models\FranchiseCase;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get dashboard statistics
        $stats = [];
        $alerts = [];

        // Total units (matches Unit Management default list)
        $stats['active_units'] = Unit::count();

        // Units with ROI achieved (calculated from real boundary data)
        $stats['roi_units'] = DB::table('units as u')
            ->where('u.purchase_cost', '>', 0)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('boundaries as b')
                    ->whereRaw('b.unit_id = u.id')
                    ->whereIn('b.status', ['paid', 'excess'])
                    ->groupBy('b.unit_id')
                    ->havingRaw('SUM(b.boundary_amount) >= u.purchase_cost');
            })
            ->count();

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

        // Active drivers — drivers table uses driver_status column
        $stats['active_drivers'] = DB::table('drivers as d')
            ->join('users as u', 'd.user_id', '=', 'u.id')
            ->where('u.is_active', true)
            ->count();

        // Average boundary rate for active units
        $stats['avg_boundary'] = DB::table('units')
            ->where('status', 'active')
            ->avg('boundary_rate') ?? 0;

        // Maintenance cost this month — real column: date_started (not maintenance_date)
        $stats['monthly_maintenance'] = DB::table('maintenance')
            ->whereMonth('date_started', now()->month)
            ->whereYear('date_started', now()->year)
            ->where('status', 'completed')
            ->sum('cost') ?? 0;

        // System alerts (unresolved)
        $alerts = DB::table('system_alerts')
            ->where('is_resolved', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Revenue trend (dynamic based on period)
        $period = $request->get('period', 30); // Default to 30 days
        $revenue_trend = collect(range($period - 1, 0))->map(function ($daysAgo) use ($period) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')->whereDate('date', $date)->sum('boundary_amount') ?? 0;
            
            // Format label based on period
            if ($period <= 7) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 30) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 90) {
                $label = now()->subDays($daysAgo)->format('M j');
            } else {
                $label = now()->subDays($daysAgo)->format('M Y');
            }
            
            return [
                'date' => $label,
                'revenue' => (float) $boundary,
            ];
        })->values()->toArray();

        // Weekly financial trend (last 7 days real data)
        $weekly_data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')->whereDate('date', $date)->sum('boundary_amount') ?? 0;
            $expenses = DB::table('expenses')->whereDate('date', $date)->sum('amount') ?? 0;
            return [
                'day'      => now()->subDays($daysAgo)->format('D'),
                'boundary' => (float) $boundary,
                'expenses' => (float) $expenses,
                'net'      => (float) ($boundary - $expenses),
            ];
        })->values()->toArray();

        $unit_status_data = [
            ['status' => 'Active',            'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['active'])->count()],
            ['status' => 'Under Maintenance', 'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['maintenance'])->count()],
            ['status' => 'Coding',            'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['coding'])->count()],
            ['status' => 'Retired',           'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['retired'])->count()],
        ];

        // Unit performance (top performing units)
        $unit_performance = DB::table('units as u')
            ->leftJoin('boundaries as b', 'u.id', '=', 'b.unit_id')
            ->select('u.unit_number', DB::raw('COALESCE(SUM(b.boundary_amount), 0) as total_boundary'), 'u.boundary_rate')
            ->where('u.status', 'active')
            ->groupBy('u.id', 'u.unit_number', 'u.boundary_rate')
            ->orderByDesc('total_boundary')
            ->limit(10)
            ->get()
            ->map(function($unit) {
                return [
                    'unit' => $unit->unit_number,
                    'performance' => (float) $unit->total_boundary,
                    'target' => (float) $unit->boundary_rate * 30, // Monthly target
                ];
            });

        // Expense breakdown by category
        $expense_breakdown = DB::table('expenses')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(function($expense) {
                return [
                    'category' => $expense->category,
                    'amount' => (float) $expense->total,
                ];
            });

        return view('dashboard', compact('stats', 'alerts', 'weekly_data', 'unit_status_data', 'revenue_trend', 'unit_performance', 'expense_breakdown'));
    }

    public function getRealTimeData()
    {
        // Get fresh dashboard statistics
        $stats = [];
        $alerts = [];

        // Total units
        $stats['active_units'] = Unit::count();

        // Units with ROI achieved
        $stats['roi_units'] = DB::table('units as u')
            ->where('u.purchase_cost', '>', 0)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('boundaries as b')
                    ->whereRaw('b.unit_id = u.id')
                    ->whereIn('b.status', ['paid', 'excess'])
                    ->groupBy('b.unit_id')
                    ->havingRaw('SUM(b.boundary_amount) >= u.purchase_cost');
            })
            ->count();

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

        // Average boundary rate
        $stats['avg_boundary'] = DB::table('units')
            ->where('status', 'active')
            ->avg('boundary_rate') ?? 0;

        // Maintenance cost this month
        $stats['monthly_maintenance'] = DB::table('maintenance')
            ->whereMonth('date_started', now()->month)
            ->whereYear('date_started', now()->year)
            ->where('status', 'completed')
            ->sum('cost') ?? 0;

        // System alerts
        $alerts = DB::table('system_alerts')
            ->where('is_resolved', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function($alert) {
                return [
                    'message' => $alert->message,
                    'severity' => $alert->severity,
                    'alert_type' => $alert->alert_type
                ];
            });

        // Weekly financial trend
        $weekly_data = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')->whereDate('date', $date)->sum('boundary_amount') ?? 0;
            $expenses = DB::table('expenses')->whereDate('date', $date)->sum('amount') ?? 0;
            return [
                'day'      => now()->subDays($daysAgo)->format('D'),
                'boundary' => (float) $boundary,
                'expenses' => (float) $expenses,
                'net'      => (float) ($boundary - $expenses),
            ];
        })->values()->toArray();

        $unit_status_data = [
            ['status' => 'Active',            'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['active'])->count()],
            ['status' => 'Under Maintenance', 'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['maintenance'])->count()],
            ['status' => 'Coding',            'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['coding'])->count()],
            ['status' => 'Retired',           'count' => DB::table('units')->whereRaw('LOWER(status) = ?', ['retired'])->count()],
        ];

        // Revenue trend (last 30 days)
        $revenue_trend = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')->whereDate('date', $date)->sum('boundary_amount') ?? 0;
            return [
                'date' => now()->subDays($daysAgo)->format('M j'),
                'revenue' => (float) $boundary,
            ];
        })->values()->toArray();

        // Unit performance (top performing units)
        $unit_performance = DB::table('units as u')
            ->leftJoin('boundaries as b', 'u.id', '=', 'b.unit_id')
            ->select('u.unit_number', DB::raw('COALESCE(SUM(b.boundary_amount), 0) as total_boundary'), 'u.boundary_rate')
            ->where('u.status', 'active')
            ->groupBy('u.id', 'u.unit_number', 'u.boundary_rate')
            ->orderByDesc('total_boundary')
            ->limit(10)
            ->get()
            ->map(function($unit) {
                return [
                    'unit' => $unit->unit_number,
                    'performance' => (float) $unit->total_boundary,
                    'target' => (float) $unit->boundary_rate * 30, // Monthly target
                ];
            });

        // Expense breakdown by category
        $expense_breakdown = DB::table('expenses')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(function($expense) {
                return [
                    'category' => $expense->category,
                    'amount' => (float) $expense->total,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'alerts' => $alerts,
            'charts' => [
                'weekly_data' => $weekly_data,
                'unit_status_data' => $unit_status_data,
                'revenue_trend' => $revenue_trend,
                'unit_performance' => $unit_performance,
                'expense_breakdown' => $expense_breakdown
            ]
        ]);
    }

    public function getRevenueTrend(Request $request)
    {
        $period = $request->get('period', 30);
        
        $revenue_trend = collect(range($period - 1, 0))->map(function ($daysAgo) use ($period) {
            $date = now()->subDays($daysAgo)->toDateString();
            $boundary = DB::table('boundaries')->whereDate('date', $date)->sum('boundary_amount') ?? 0;
            
            // Format label based on period
            if ($period <= 7) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 30) {
                $label = now()->subDays($daysAgo)->format('M j');
            } elseif ($period <= 90) {
                $label = now()->subDays($daysAgo)->format('M j');
            } else {
                $label = now()->subDays($daysAgo)->format('M Y');
            }
            
            return [
                'date' => $label,
                'revenue' => (float) $boundary,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'data' => $revenue_trend
        ]);
    }

    public function getUnitsOverview()
    {
        try {
            // Get all units with complete real information
            $units = DB::table('units')
                ->select('id', 'unit_number', 'status', 'boundary_rate', 'purchase_cost', 'plate_number', 'driver_id')
                ->orderBy('unit_number')
                ->get()
                ->map(function($unit) {
                    // Get total boundary for this unit from real data
                    $totalBoundary = DB::table('boundaries')
                        ->where('unit_id', $unit->id)
                        ->sum('boundary_amount') ?? 0;
                    
                    // Get today's boundary for real-time data
                    $todayBoundary = DB::table('boundaries')
                        ->where('unit_id', $unit->id)
                        ->whereDate('date', now()->toDateString())
                        ->sum('boundary_amount') ?? 0;
                    
                    // Get driver information
                    $driverName = 'N/A';
                    if ($unit->driver_id) {
                        $driver = DB::table('drivers as d')
                            ->join('users as u', 'd.user_id', '=', 'u.id')
                            ->where('d.id', $unit->driver_id)
                            ->first();
                        $driverName = $driver ? $driver->name : 'N/A';
                    }
                    
                    // Calculate ROI percentage based on real data
                    $roiPercentage = 0;
                    if ($unit->purchase_cost > 0 && $totalBoundary > 0) {
                        $roiPercentage = min(100, round(($totalBoundary / $unit->purchase_cost) * 100, 2));
                    }
                    
                    // Calculate days to ROI based on real performance
                    $daysToROI = 0;
                    if ($unit->purchase_cost > 0 && $totalBoundary > 0 && $roiPercentage < 100) {
                        // Method 1: Calculate based on recent 30-day average
                        $recent30DaysBoundary = DB::table('boundaries')
                            ->where('unit_id', $unit->id)
                            ->where('boundary_amount', '>', 0)
                            ->whereDate('date', '>=', now()->subDays(30)->toDateString())
                            ->sum('boundary_amount') ?? 0;
                        
                        // Method 2: Calculate based on last 10 days average
                        $last10DaysBoundary = DB::table('boundaries')
                            ->where('unit_id', $unit->id)
                            ->where('boundary_amount', '>', 0)
                            ->whereDate('date', '>=', now()->subDays(10)->toDateString())
                            ->sum('boundary_amount') ?? 0;
                        
                        // Method 3: Calculate based on last 7 days average
                        $last7DaysBoundary = DB::table('boundaries')
                            ->where('unit_id', $unit->id)
                            ->where('boundary_amount', '>', 0)
                            ->whereDate('date', '>=', now()->subDays(7)->toDateString())
                            ->sum('boundary_amount') ?? 0;
                        
                        // Choose the most reliable method
                        $dailyAverage = 0;
                        
                        if ($last7DaysBoundary > 0) {
                            // Use last 7 days if available (most recent)
                            $dailyAverage = $last7DaysBoundary / 7;
                        } elseif ($last10DaysBoundary > 0) {
                            // Use last 10 days
                            $dailyAverage = $last10DaysBoundary / 10;
                        } elseif ($recent30DaysBoundary > 0) {
                            // Use last 30 days
                            $dailyAverage = $recent30DaysBoundary / 30;
                        } else {
                            // Fallback to overall average
                            $activeDays = DB::table('boundaries')
                                ->where('unit_id', $unit->id)
                                ->where('boundary_amount', '>', 0)
                                ->count();
                            if ($activeDays > 0) {
                                $dailyAverage = $totalBoundary / $activeDays;
                            }
                        }
                        
                        // Calculate days to ROI with accuracy improvements
                        if ($dailyAverage > 0) {
                            $remainingAmount = $unit->purchase_cost - $totalBoundary;
                            $daysToROI = ceil($remainingAmount / $dailyAverage);
                            
                            // Cap at maximum 365 days (1 year) for realistic estimation
                            $daysToROI = min($daysToROI, 365);
                            
                            // If ROI is very close (within 5%), show as "Almost there"
                            if ($daysToROI <= 5) {
                                $daysToROI = 0; // Will be handled as "Almost there"
                            }
                        } else {
                            $daysToROI = 999; // No recent activity indicator
                        }
                    }
                    
                    return [
                        'id' => $unit->id,
                        'unit_number' => $unit->unit_number,
                        'plate_number' => $unit->plate_number,
                        'status' => strtolower($unit->status),
                        'boundary_rate' => (float) $unit->boundary_rate,
                        'total_boundary' => (float) $totalBoundary,
                        'today_boundary' => (float) $todayBoundary,
                        'purchase_cost' => (float) $unit->purchase_cost,
                        'driver_name' => $driverName,
                        'driver_id' => $unit->driver_id,
                        'roi_percentage' => $roiPercentage,
                        'roi_achieved' => $roiPercentage >= 100,
                        'days_to_roi' => $daysToROI,
                        'last_activity' => $this->getLastActivity($unit->id),
                        'performance_rating' => $this->getPerformanceRating($roiPercentage)
                    ];
                });

            // Calculate real stats from actual data
            $stats = [
                'total_units' => $units->count(),
                'active_units' => $units->where('status', 'active')->count(),
                'roi_units' => $units->where('roi_achieved', true)->count(),
                'coding_units' => $units->where('status', 'coding')->count(),
                'maintenance_units' => $units->where('status', 'maintenance')->count(),
                'retired_units' => $units->where('status', 'retired')->count(),
                'avg_roi' => $units->avg('roi_percentage') ?: 0,
                'total_investment' => $units->sum('purchase_cost'),
                'total_collected' => $units->sum('total_boundary'),
                'today_collected' => $units->sum('today_boundary')
            ];

            return response()->json([
                'success' => true,
                'units' => $units,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading units overview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading units data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get last activity for a unit
     */
    private function getLastActivity($unitId)
    {
        $lastBoundary = DB::table('boundaries')
            ->where('unit_id', $unitId)
            ->orderBy('date', 'desc')
            ->first();
            
        return $lastBoundary ? $lastBoundary->date : null;
    }

    /**
     * Get performance rating based on ROI
     */
    private function getPerformanceRating($roiPercentage)
    {
        if ($roiPercentage >= 100) return 'excellent';
        if ($roiPercentage >= 75) return 'good';
        if ($roiPercentage >= 50) return 'average';
        return 'growing';
    }

    /**
     * Get daily boundary collections with detailed information
     */
    public function getDailyBoundaryCollections()
    {
        try {
            // Get boundary collections with complete information
            $collections = DB::table('boundaries as b')
                ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->leftJoin('users as du', 'd.user_id', '=', 'du.id')
                ->select([
                    'b.id',
                    'b.unit_id',
                    'b.boundary_amount',
                    'b.date',
                    'u.unit_number',
                    'u.plate_number',
                    'du.name as driver_name',
                    'd.id as driver_id'
                ])
                ->orderBy('b.date', 'desc')
                ->orderBy('b.id', 'desc')
                ->get()
                ->map(function($collection) {
                    return [
                        'id' => $collection->id,
                        'unit_id' => $collection->unit_id,
                        'unit_number' => $collection->unit_number,
                        'plate_number' => $collection->plate_number,
                        'driver_name' => $collection->driver_name,
                        'driver_id' => $collection->driver_id,
                        'boundary_amount' => (float) $collection->boundary_amount,
                        'date' => $collection->date,
                        'time' => 'N/A', // Default value since time column doesn't exist
                        'location' => 'Main Office', // Default value since location column doesn't exist
                        'status' => 'verified' // Default value since status column doesn't exist
                    ];
                });

            // Calculate statistics
            $stats = [
                'total_collections' => $collections->count(),
                'unique_units' => $collections->pluck('unit_id')->unique()->count(),
                'unique_drivers' => $collections->pluck('driver_id')->unique()->count(),
                'total_amount' => $collections->sum('boundary_amount'),
                'today_collections' => $collections->where('date', now()->toDateString())->count(),
                'today_amount' => $collections->where('date', now()->toDateString())->sum('boundary_amount')
            ];

            return response()->json([
                'success' => true,
                'collections' => $collections,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading daily boundary collections: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading boundary collections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get net income details with breakdown
     */
    public function getNetIncomeDetails()
    {
        try {
            // Get income data from boundaries
            $incomeData = DB::table('boundaries as b')
                ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->leftJoin('users as du', 'd.user_id', '=', 'du.id')
                ->select([
                    'b.id',
                    'b.unit_id',
                    'b.boundary_amount',
                    'b.date',
                    'u.unit_number',
                    'u.plate_number',
                    'du.name as driver_name',
                    'd.id as driver_id'
                ])
                ->orderBy('b.date', 'desc')
                ->orderBy('b.id', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'income',
                        'description' => 'Boundary Collection - ' . $item->unit_number,
                        'category' => 'Boundary Income',
                        'amount' => (float) $item->boundary_amount,
                        'date' => $item->date,
                        'source' => $item->unit_number,
                        'reference' => 'Boundary #' . $item->id,
                        'unit_number' => $item->unit_number,
                        'driver_name' => $item->driver_name
                    ];
                });

            // Initialize expense data as empty collection
            $expenseData = collect();
            $expenseTable = null;

            // Try different expense table names - but handle gracefully
            try {
                // Check for office_expenses table
                if (Schema::hasTable('office_expenses')) {
                    $expenseTable = 'office_expenses';
                }
                // Check for expenses table
                elseif (Schema::hasTable('expenses')) {
                    $expenseTable = 'expenses';
                }
                // Check for office_expense table (singular)
                elseif (Schema::hasTable('office_expense')) {
                    $expenseTable = 'office_expense';
                }

                if ($expenseTable) {
                    $expenseData = DB::table($expenseTable . ' as oe')
                        ->leftJoin('users as u', 'oe.user_id', '=', 'u.id')
                        ->select([
                            'oe.id',
                            'oe.expense_type',
                            'oe.amount',
                            'oe.description',
                            'oe.date',
                            'oe.user_id',
                            'u.name as user_name'
                        ])
                        ->orderBy('oe.date', 'desc')
                        ->orderBy('oe.id', 'desc')
                        ->get()
                        ->map(function($item) {
                            return [
                                'id' => $item->id,
                                'type' => 'expense',
                                'description' => $item->description || $item->expense_type,
                                'category' => $item->expense_type,
                                'amount' => (float) $item->amount,
                                'date' => $item->date,
                                'source' => $item->user_name,
                                'reference' => 'Expense #' . $item->id,
                                'expense_type' => $item->expense_type,
                                'user_name' => $item->user_name
                            ];
                        });
                }
            } catch (\Exception $expenseError) {
                \Log::error('Error loading expense data: ' . $expenseError->getMessage());
                // Continue with empty expense data
                $expenseData = collect();
            }

            // Combine income and expense data
            $allData = $incomeData->concat($expenseData)
                ->sortByDesc('date')
                ->values();

            // Calculate statistics
            $totalIncome = $incomeData->sum('amount');
            $totalExpenses = $expenseData->sum('amount');
            $netIncome = $totalIncome - $totalExpenses;
            $profitMargin = $totalIncome > 0 ? (($netIncome / $totalIncome) * 100) : 0;

            $stats = [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'profit_margin' => $profitMargin,
                'income_count' => $incomeData->count(),
                'expense_count' => $expenseData->count(),
                'total_transactions' => $allData->count(),
                'expense_table_used' => $expenseTable,
                'debug_info' => [
                    'income_data_count' => $incomeData->count(),
                    'expense_data_count' => $expenseData->count(),
                    'expense_table_found' => $expenseTable ? 'yes' : 'no'
                ]
            ];

            return response()->json([
                'success' => true,
                'income_data' => $allData,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading net income details: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading net income details: ' . $e->getMessage(),
                'debug_info' => [
                    'error_type' => get_class($e),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Get maintenance units with detailed information
     */
    public function getMaintenanceUnits()
    {
        try {
            $hasMaintenances = Schema::hasTable('maintenances');
            $hasDrivers = Schema::hasTable('drivers');

            $unitsQuery = DB::table('units as u');

            if ($hasDrivers) {
                $unitsQuery
                    ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                    ->leftJoin('users as du', 'd.user_id', '=', 'du.id');
            }

            if ($hasMaintenances) {
                $unitsQuery->leftJoin('maintenances as m', 'u.id', '=', 'm.unit_id');
            }

            $select = [
                'u.id',
                'u.unit_number',
                'u.plate_number',
                'u.status',
                'u.purchase_cost',
                'u.boundary_rate',
                'u.created_at',
                $hasDrivers ? 'du.name as driver_name' : DB::raw('NULL as driver_name'),
                $hasMaintenances ? 'm.id as maintenance_id' : DB::raw('NULL as maintenance_id'),
                $hasMaintenances ? 'm.maintenance_type' : DB::raw('NULL as maintenance_type'),
                $hasMaintenances ? 'm.description' : DB::raw('NULL as description'),
                $hasMaintenances ? 'm.start_date' : DB::raw('NULL as start_date'),
                $hasMaintenances ? 'm.end_date' : DB::raw('NULL as end_date'),
                $hasMaintenances ? 'm.status as maintenance_status' : DB::raw('NULL as maintenance_status'),
                $hasMaintenances ? 'm.cost as maintenance_cost' : DB::raw('NULL as maintenance_cost'),
            ];

            $maintenanceUnits = $unitsQuery
                ->select($select)
                ->where('u.status', '=', 'maintenance')
                ->when($hasMaintenances, function ($q) {
                    $q->orderBy('m.start_date', 'desc');
                }, function ($q) {
                    $q->orderBy('u.id', 'desc');
                })
                ->get()
                ->map(function($unit) {
                    $startDate = data_get($unit, 'start_date');
                    $endDate = data_get($unit, 'end_date');
                    return [
                        'id' => $unit->id,
                        'unit_number' => $unit->unit_number,
                        'plate_number' => $unit->plate_number,
                        'status' => $unit->status,
                        'driver_name' => $unit->driver_name,
                        'maintenance_type' => $unit->maintenance_type ?: 'Maintenance',
                        'description' => $unit->description ?: 'No description available',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'estimated_completion' => $endDate ?: 'Not specified',
                        'maintenance_status' => $unit->maintenance_status ?: 'Ongoing',
                        'maintenance_cost' => (float) ($unit->maintenance_cost ?? 0),
                        'purchase_cost' => (float) ($unit->purchase_cost ?? 0),
                        'boundary_rate' => (float) ($unit->boundary_rate ?? 0)
                    ];
                });

            // Calculate statistics
            $totalMaintenance = $maintenanceUnits->count();
            $completedMaintenance = $maintenanceUnits->where('maintenance_status', 'completed')->count();
            $pendingMaintenance = $maintenanceUnits->where('maintenance_status', 'pending')->count();
            $avgMaintenanceDays = $totalMaintenance > 0 ? 
                $maintenanceUnits->filter(function($unit) {
                    return !empty($unit['start_date']) && !empty($unit['end_date']);
                })->map(function($unit) {
                    return Carbon::parse($unit['end_date'])->diffInDays(Carbon::parse($unit['start_date']));
                })->avg() : 0;

            $stats = [
                'total_maintenance' => $totalMaintenance,
                'completed_maintenance' => $completedMaintenance,
                'pending_maintenance' => $pendingMaintenance,
                'avg_maintenance_days' => round($avgMaintenanceDays, 1),
                'total_maintenance_cost' => $maintenanceUnits->sum('maintenance_cost')
            ];

            return response()->json([
                'success' => true,
                'units' => $maintenanceUnits,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading maintenance units: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading maintenance units: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active drivers with detailed information
     */
    public function getActiveDrivers()
    {
        try {
            if (!Schema::hasTable('drivers')) {
                return response()->json([
                    'success' => true,
                    'drivers' => [],
                    'stats' => [
                        'active_drivers' => 0,
                        'assigned_units' => 0,
                        'avg_boundary' => 0,
                        'top_performers' => 0,
                        'total_boundary_collected' => 0
                    ],
                    'data_source' => 'real_database',
                    'last_updated' => now()->toDateTimeString()
                ]);
            }

            $select = [
                'd.id',
                'd.user_id',
                'u.name',
                'u.email',
                DB::raw('COUNT(DISTINCT unit.id) as assigned_units'),
                DB::raw('COALESCE(SUM(b.boundary_amount), 0) as total_boundary'),
                DB::raw('COALESCE(AVG(b.boundary_amount), 0) as avg_boundary'),
            ];
            $groupBy = ['d.id', 'd.user_id', 'u.name', 'u.email'];

            if (Schema::hasColumn('drivers', 'hire_date')) {
                $select[] = 'd.hire_date';
                $groupBy[] = 'd.hire_date';
            } else {
                $select[] = DB::raw('NULL as hire_date');
            }

            if (Schema::hasColumn('drivers', 'license_number')) {
                $select[] = 'd.license_number';
                $groupBy[] = 'd.license_number';
            } else {
                $select[] = DB::raw('NULL as license_number');
            }

            if (Schema::hasColumn('drivers', 'phone')) {
                $select[] = 'd.phone';
                $groupBy[] = 'd.phone';
            } else {
                $select[] = DB::raw('NULL as phone');
            }

            if (Schema::hasColumn('drivers', 'address')) {
                $select[] = 'd.address';
                $groupBy[] = 'd.address';
            } else {
                $select[] = DB::raw('NULL as address');
            }

            $query = DB::table('drivers as d')
                ->leftJoin('users as u', 'd.user_id', '=', 'u.id')
                ->leftJoin('units as unit', 'd.id', '=', 'unit.driver_id')
                ->leftJoin('boundaries as b', 'unit.id', '=', 'b.unit_id')
                ->select($select);

            if (Schema::hasColumn('drivers', 'status')) {
                $query->where('d.status', '=', 'active');
            }

            $activeDrivers = $query
                ->groupBy($groupBy)
                ->orderBy('u.name', 'asc')
                ->get()
                ->map(function($driver) {
                    $avgBoundary = (float) ($driver->avg_boundary ?? 0);
                    $performanceRating = 'average';
                    if ($avgBoundary >= 2000) $performanceRating = 'excellent';
                    elseif ($avgBoundary >= 1500) $performanceRating = 'good';
                    elseif ($avgBoundary >= 1000) $performanceRating = 'average';
                    else $performanceRating = 'needs_improvement';

                    return [
                        'id' => $driver->id,
                        'name' => $driver->name,
                        'email' => $driver->email,
                        'license_number' => $driver->license_number,
                        'phone' => $driver->phone,
                        'address' => $driver->address,
                        'hire_date' => $driver->hire_date,
                        'assigned_units' => (int) ($driver->assigned_units ?? 0),
                        'total_boundary' => (float) ($driver->total_boundary ?? 0),
                        'avg_boundary' => $avgBoundary,
                        'performance_rating' => $performanceRating
                    ];
                });

            // Calculate statistics
            $totalActiveDrivers = $activeDrivers->count();
            $totalAssignedUnits = $activeDrivers->sum('assigned_units');
            $avgBoundaryPerDriver = $totalActiveDrivers > 0 ? 
                $activeDrivers->avg('avg_boundary') : 0;
            $topPerformers = $activeDrivers->where('performance_rating', 'excellent')->count();

            $stats = [
                'active_drivers' => $totalActiveDrivers,
                'assigned_units' => $totalAssignedUnits,
                'avg_boundary' => round($avgBoundaryPerDriver, 2),
                'top_performers' => $topPerformers,
                'total_boundary_collected' => $activeDrivers->sum('total_boundary')
            ];

            return response()->json([
                'success' => true,
                'drivers' => $activeDrivers,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading active drivers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading active drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coding units with detailed information
     */
    public function getCodingUnits()
    {
        try {
            $hasMaintenances = Schema::hasTable('maintenances');
            $hasDrivers = Schema::hasTable('drivers');

            $unitsQuery = DB::table('units as u');

            if ($hasDrivers) {
                $unitsQuery
                    ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                    ->leftJoin('users as du', 'd.user_id', '=', 'du.id');
            }

            if ($hasMaintenances) {
                $unitsQuery->leftJoin('maintenances as m', 'u.id', '=', 'm.unit_id');
            }

            $select = [
                'u.id',
                'u.unit_number',
                'u.plate_number',
                'u.status',
                'u.purchase_cost',
                'u.boundary_rate',
                'u.created_at',
                $hasDrivers ? 'du.name as driver_name' : DB::raw('NULL as driver_name'),
                $hasMaintenances ? 'm.id as coding_id' : DB::raw('NULL as coding_id'),
                $hasMaintenances ? 'm.maintenance_type as coding_type' : DB::raw('NULL as coding_type'),
                $hasMaintenances ? 'm.description' : DB::raw('NULL as description'),
                $hasMaintenances ? 'm.start_date' : DB::raw('NULL as start_date'),
                $hasMaintenances ? 'm.end_date' : DB::raw('NULL as end_date'),
                $hasMaintenances ? 'm.status as coding_status' : DB::raw('NULL as coding_status'),
                $hasMaintenances ? 'm.cost as coding_cost' : DB::raw('NULL as coding_cost'),
            ];

            $codingUnits = $unitsQuery
                ->select($select)
                ->where('u.status', '=', 'coding')
                ->when($hasMaintenances, function ($q) {
                    $q->orderBy('m.start_date', 'desc');
                }, function ($q) {
                    $q->orderBy('u.id', 'desc');
                })
                ->get()
                ->map(function($unit) {
                    $startDate = data_get($unit, 'start_date');
                    $endDate = data_get($unit, 'end_date');
                    return [
                        'id' => $unit->id,
                        'unit_number' => $unit->unit_number,
                        'plate_number' => $unit->plate_number,
                        'status' => $unit->status,
                        'driver_name' => $unit->driver_name,
                        'coding_type' => $unit->coding_type ?: 'Coding',
                        'description' => $unit->description ?: 'No description available',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'estimated_completion' => $endDate ?: 'Not specified',
                        'coding_status' => $unit->coding_status ?: 'Ongoing',
                        'coding_cost' => (float) ($unit->coding_cost ?? 0),
                        'purchase_cost' => (float) ($unit->purchase_cost ?? 0),
                        'boundary_rate' => (float) ($unit->boundary_rate ?? 0)
                    ];
                });

            // Calculate statistics
            $totalCoding = $codingUnits->count();
            $completedCoding = $codingUnits->where('coding_status', 'completed')->count();
            $pendingCoding = $codingUnits->where('coding_status', 'pending')->count();
            $avgCodingDays = $totalCoding > 0 ? 
                $codingUnits->filter(function($unit) {
                    return !empty($unit['start_date']) && !empty($unit['end_date']);
                })->map(function($unit) {
                    return Carbon::parse($unit['end_date'])->diffInDays(Carbon::parse($unit['start_date']));
                })->avg() : 0;

            $stats = [
                'total_coding' => $totalCoding,
                'completed_coding' => $completedCoding,
                'pending_coding' => $pendingCoding,
                'avg_coding_days' => round($avgCodingDays, 1),
                'total_coding_cost' => $codingUnits->sum('coding_cost')
            ];

            return response()->json([
                'success' => true,
                'units' => $codingUnits,
                'stats' => $stats,
                'data_source' => 'real_database',
                'last_updated' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading coding units: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading coding units: ' . $e->getMessage()
            ], 500);
        }
    }
}
