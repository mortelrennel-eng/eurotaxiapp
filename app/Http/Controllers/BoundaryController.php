<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Boundary;
use Carbon\Carbon;

use App\Traits\CalculatesBoundary;

class BoundaryController extends Controller
{
    use CalculatesBoundary;
    /**
     * Display a listing of boundary records.
     */
    public function index(Request $request)
    {
        $search        = $request->get('search', '');
        $date_filter   = $request->get('date', date('Y-m-d')); // Default to today
        $status_filter = $request->get('status', '');
        $page          = max(1, (int) $request->get('page', 1));
        $limit         = 10;
        $offset        = ($page - 1) * $limit;

        // Build query joining units and drivers tables
        $query = DB::table('boundaries as b')
            ->whereNull('b.deleted_at')
            ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
            ->leftJoin('users as creator', 'b.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'b.updated_by', '=', 'editor.id')
            ->select(
                'b.*',
                'u.plate_number',
                'u.year as unit_year',
                'u.coding_day as unit_coding_day',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver_name"),
                'creator.full_name as creator_name',
                'editor.full_name as editor_name'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('u.plate_number', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))"), 'like', "%{$search}%");
            });
        }

        if (!empty($date_filter)) {
            $query->whereDate('b.date', $date_filter);
        }

        if (!empty($status_filter)) {
            $query->where('b.status', $status_filter);
        }

        $total_boundaries = $query->count();

        $boundaries = $query
            ->orderByDesc('b.date')
            ->orderByDesc('b.created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Get units for dropdowns
        $units = DB::table('units')
            ->whereNull('deleted_at')
            ->where('status', '!=', 'retired')
            ->select('id', 'plate_number', 'make', 'model', 'year', 'boundary_rate', 'coding_day', 'driver_id', 'secondary_driver_id', 'current_turn_driver_id', 'last_swapping_at', 'shift_deadline_at')
            ->orderBy('plate_number')
            ->get()
            ->map(function ($unit) {
                $unitArray = (array) $unit;
                $unitArray['make_model'] = ($unitArray['make'] ?? '') . ' ' . ($unitArray['model'] ?? '');
                return $unitArray;
            })
            ->toArray();

        // Get all drivers with their current unit assignments
        $all_drivers = DB::select("
            SELECT d.id, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name, 
                   COALESCE(ua.plate_number, 'No Assignment') as current_unit,
                   COALESCE(ua.plate_number, '') as current_plate,
                   (SELECT COUNT(*) FROM units WHERE (driver_id = d.id OR secondary_driver_id = d.id) AND deleted_at IS NULL) as assigned_units_count,
                   (SELECT GREATEST(0, COALESCE(SUM(shortage),0) - COALESCE(SUM(excess),0)) FROM boundaries WHERE driver_id = d.id AND deleted_at IS NULL) as net_shortage
            FROM drivers d 
            LEFT JOIN units ua ON (d.id = ua.driver_id OR d.id = ua.secondary_driver_id) AND ua.deleted_at IS NULL
            WHERE d.deleted_at IS NULL
            ORDER BY 
                CASE WHEN ua.plate_number IS NOT NULL THEN 1 ELSE 0 END,
                d.last_name, d.first_name
        ");
        $all_drivers = array_map(function($d) { return (array) $d; }, $all_drivers);

        // Assigned drivers
        $assigned_drivers = DB::select("
            SELECT d.id, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name, 
                   ua.plate_number as current_unit,
                   ua.plate_number as current_plate
            FROM drivers d 
            LEFT JOIN units ua ON (d.id = ua.driver_id OR d.id = ua.secondary_driver_id) AND ua.deleted_at IS NULL
            WHERE ua.plate_number IS NOT NULL
            AND d.deleted_at IS NULL
            ORDER BY ua.plate_number, d.last_name, d.first_name
        ");
        $assigned_drivers = array_map(function($d) { return (array) $d; }, $assigned_drivers);

        // Unit drivers
        $unit_drivers = [];
        foreach ($units as $unit) {
            $unit_id = $unit['id'];
            $res = DB::select("
                SELECT d.id, CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as name, 
                       ua.plate_number as current_plate,
                       ua.plate_number as current_unit
                FROM drivers d 
                LEFT JOIN units ua ON (d.id = ua.driver_id OR d.id = ua.secondary_driver_id)
                WHERE ua.id = ? AND d.deleted_at IS NULL
                ORDER BY d.last_name, d.first_name
            ", [$unit_id]);
            $unit_drivers[$unit_id] = array_map(function($d) { return (array) $d; }, $res);
        }

        $total_pages = ceil($total_boundaries / $limit);
        $pagination = [
            'page'           => $page,
            'total_items'    => $total_boundaries,
            'total_pages'    => $total_pages,
            'items_per_page' => $limit,
            'offset'         => $offset,
            'has_prev'       => $page > 1,
            'prev_page'      => $page - 1,
            'has_next'       => $page < $total_pages,
            'next_page'      => $page + 1,
        ];

        // Ensure we pass $boundaries as array as backup ui expects arrays for json encoding
        $boundary_rules = DB::table('boundary_rules')->get();
        $boundariesArray = [];
        foreach ($boundaries as $b) {
            $recordDate = Carbon::parse($b->date);
            $dayOfWeek = $recordDate->format('l');
            
            // Temporary override to simulate date-specific pricing
            // We use our trait but we have to handle the "today" override if needed
            // Actually, we can just pass the day if we modify the trait, but for now 
            // since the trait uses date('l'), we'll check if it's the record's coding day manually or 
            // we simulate it.
            
            $pricing = $this->getCurrentPricing([
                'year' => $b->unit_year,
                'plate_number' => $b->plate_number,
                'boundary_rate' => $b->boundary_amount, // Use the target recorded
                'coding_day' => $b->unit_coding_day
            ], $boundary_rules);

            // Re-calculate specifically for the record's day if it's not today
            if ($dayOfWeek === 'Saturday') {
                $rule = $boundary_rules->where('start_year', '<=', $b->unit_year)->where('end_year', '>=', $b->unit_year)->first();
                $pricing['label'] = 'Saturday Discount';
                $pricing['type'] = 'discount';
            } elseif ($dayOfWeek === 'Sunday') {
                $pricing['label'] = 'Sunday Discount';
                $pricing['type'] = 'discount';
            } else {
                // Coding check for that day
                $cDay = $pricing['coding_day'] ?? null;
                if ($cDay && strtolower($dayOfWeek) === strtolower($cDay)) {
                    $pricing['label'] = 'Coding Rate';
                    $pricing['type'] = 'coding';
                } else {
                    $pricing['label'] = 'Regular Rate';
                    $pricing['type'] = 'regular';
                }
            }

            $item = (array) $b;
            $item['rate_label'] = $pricing['label'];
            $item['rate_type'] = $pricing['type'];
            $boundariesArray[] = $item;
        }

        return view('boundaries.index', compact(
            'boundaries', 
            'pagination', 
            'page', 
            'search', 
            'date_filter',
            'status_filter', 
            'units', 
            'all_drivers', 
            'assigned_drivers',
            'unit_drivers',
            'boundary_rules',
            'boundariesArray'
        ));
    }

    /**
     * Store a newly created boundary record OR update existing if id is set.
     */
    public function store(Request $request)
    {
        $action = $request->input('action', '');
        
        if ($action === 'add_boundary') {
            $unit_id         = (int) $request->input('unit_id', 0);
            $driver_id       = (int) $request->input('driver_id', 0);
            $date            = $request->input('date', date('Y-m-d'));
            $boundary_amount = (float) $request->input('boundary_amount', 0);
            $actual_boundary = (float) $request->input('actual_boundary', 0);
            $notes           = $request->input('notes', '');
            
            if ($unit_id > 0 && $driver_id > 0 && $boundary_amount > 0) {
                // Check duplicate
                $existing = DB::table('boundaries')->where('unit_id', $unit_id)->where('date', $date)->first();
                if ($existing) {
                    return back()->with('error', 'Boundary record already exists for this unit and date');
                } else {
                    $shortage = max(0, $boundary_amount - $actual_boundary);
                    $excess   = max(0, $actual_boundary - $boundary_amount);
                    $status   = $shortage > 0 ? 'shortage' : ($excess > 0 ? 'excess' : 'paid');

                    $unit = \App\Models\Unit::find($unit_id);
                    $is_extra_driver = false;
                    $expected_driver_id = $unit ? $unit->current_turn_driver_id : $driver_id;
                    $has_incentive = true;

                    if ($unit) {
                        $now = now();
                        $current_deadline = $unit->shift_deadline_at ? Carbon::parse($unit->shift_deadline_at) : $now;
                        $has_incentive = true;

                        // Strict Incentive Check: Must be before or exactly on the fixed deadline
                        if ($now->greaterThan($current_deadline)) {
                            $has_incentive = false;
                        }

                        if ($unit->driver_id !== $driver_id && $unit->secondary_driver_id !== $driver_id) {
                            $is_extra_driver = true;
                        }

                        // Determine Next Turn Driver (Toka)
                        $next_turn_driver_id = $unit->current_turn_driver_id;
                        if (!empty($unit->secondary_driver_id)) {
                            if ($driver_id === $unit->driver_id) {
                                $next_turn_driver_id = $unit->secondary_driver_id;
                            } else {
                                $next_turn_driver_id = $unit->driver_id;
                            }
                        } else {
                            $next_turn_driver_id = $unit->driver_id;
                        }

                        // Strict Deadline Pivot Logic:
                        // New deadline is Old Deadline + 24h.
                        // If the unit was offline for > 48h (e.g. maintenance), we reset the rhythm to Start Now + 24h.
                        $next_deadline = $current_deadline->copy()->addHours(24);
                        if ($now->diffInHours($current_deadline) > 48) {
                            $next_deadline = $now->copy()->addHours(24);
                        }

                        $unit->update([
                            'current_turn_driver_id' => $next_turn_driver_id,
                            'last_swapping_at' => $now, // actual record timestamp
                            'shift_deadline_at' => $next_deadline,
                        ]);
                    }

                    Boundary::create([
                        'unit_id'         => $unit_id,
                        'driver_id'       => $driver_id,
                        'expected_driver_id' => $expected_driver_id,
                        'date'            => $date,
                        'boundary_amount' => $boundary_amount,
                        'actual_boundary' => $actual_boundary,
                        'shortage'        => $shortage,
                        'excess'          => $excess,
                        'status'          => $status,
                        'notes'           => $notes,
                        'is_extra_driver' => $is_extra_driver,
                        'has_incentive'   => $has_incentive,
                        'recorded_by'     => Auth::id(),
                    ]);
                    return redirect()->route('boundaries.index')->with('success', 'Boundary record added successfully');
                }
            } else {
                return back()->with('error', 'Please fill in all required fields');
            }
        }

        if ($action === 'update_boundary') {
            $id              = (int) $request->input('id', 0);
            $boundary_amount = (float) $request->input('boundary_amount', 0);
            $actual_boundary = (float) $request->input('actual_boundary', 0);
            $notes           = $request->input('notes', '');
            
            if ($id > 0 && $boundary_amount > 0) {
                $shortage = max(0, $boundary_amount - $actual_boundary);
                $excess   = max(0, $actual_boundary - $boundary_amount);
                $status   = $shortage > 0 ? 'shortage' : ($excess > 0 ? 'excess' : 'paid');

                $boundary = Boundary::find($id);
                if ($boundary) {
                    $boundary->update([
                        'boundary_amount' => $boundary_amount,
                        'actual_boundary' => $actual_boundary,
                        'shortage'        => $shortage,
                        'excess'          => $excess,
                        'status'          => $status,
                        'notes'           => $notes,
                    ]);
                }
                return redirect()->route('boundaries.index')->with('success', 'Boundary record updated successfully');
            } else {
                return back()->with('error', 'Please fill in all required fields');
            }
        }

        return redirect()->route('boundaries.index');
    }

    public function edit($id) { return redirect()->route('boundaries.index'); }
    public function update(Request $request, $id) { return redirect()->route('boundaries.index'); }
    public function destroy($id)
    {
        $boundary = Boundary::findOrFail($id);
        $boundary->delete();
        return redirect()->route('boundaries.index')->with('success', 'Boundary record archived.');
    }
    public function show($id) { return redirect()->route('boundaries.index'); }
    public function create() { return redirect()->route('boundaries.index'); }
}
