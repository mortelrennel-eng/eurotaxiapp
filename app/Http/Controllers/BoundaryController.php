<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Boundary;
use Carbon\Carbon;

class BoundaryController extends Controller
{
    /**
     * Display a listing of boundary records.
     */
    public function index(Request $request)
    {
        $search        = $request->get('search', '');
        $date_filter   = $request->get('date', '');
        $status_filter = $request->get('status', '');
        $page          = max(1, (int) $request->get('page', 1));
        $limit         = 10;
        $offset        = ($page - 1) * $limit;

        // Build query joining units and drivers tables
        $query = DB::table('boundaries as b')
            ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
            ->leftJoin('users as usr', 'd.user_id', '=', 'usr.id')
            ->leftJoin('users as creator', 'b.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'b.updated_by', '=', 'editor.id')
            ->select(
                'b.*',
                'u.unit_number',
                'u.plate_number',
                'usr.full_name as driver_name',
                'creator.full_name as creator_name',
                'editor.full_name as editor_name'
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('u.unit_number', 'like', "%{$search}%")
                  ->orWhere('u.plate_number', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(usr.full_name, '')"), 'like', "%{$search}%");
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
            ->where('status', '!=', 'retired')
            ->select('id', 'unit_number', 'plate_number', 'make', 'model', 'boundary_rate', 'coding_day', 'driver_id', 'secondary_driver_id')
            ->orderBy('unit_number')
            ->get()
            ->map(function ($unit) {
                $unitArray = (array) $unit;
                $unitArray['make_model'] = ($unitArray['make'] ?? '') . ' ' . ($unitArray['model'] ?? '');
                return $unitArray;
            })
            ->toArray();

        // Get all drivers with their current unit assignments
        $all_drivers = DB::select("
            SELECT d.id, d.user_id, u.full_name as name, 
                   COALESCE(ua.unit_number, 'No Assignment') as current_unit,
                   COALESCE(ua.plate_number, '') as current_plate,
                   (SELECT COUNT(*) FROM units WHERE driver_id = d.id OR secondary_driver_id = d.id) as assigned_units_count
            FROM drivers d 
            LEFT JOIN users u ON d.user_id = u.id 
            LEFT JOIN units ua ON (d.user_id = ua.driver_id OR d.user_id = ua.secondary_driver_id)
            WHERE u.role = 'driver' AND u.is_active = TRUE 
            ORDER BY 
                CASE WHEN ua.unit_number IS NOT NULL THEN 1 ELSE 0 END,
                u.full_name
        ");
        $all_drivers = array_map(function($d) { return (array) $d; }, $all_drivers);

        // Assigned drivers
        $assigned_drivers = DB::select("
            SELECT d.id, d.user_id, u.full_name as name, 
                   ua.unit_number as current_unit,
                   ua.plate_number as current_plate
            FROM drivers d 
            LEFT JOIN users u ON d.user_id = u.id 
            LEFT JOIN units ua ON (d.user_id = ua.driver_id OR d.user_id = ua.secondary_driver_id)
            WHERE u.role = 'driver' AND u.is_active = TRUE 
            AND ua.unit_number IS NOT NULL
            ORDER BY ua.unit_number, u.full_name
        ");
        $assigned_drivers = array_map(function($d) { return (array) $d; }, $assigned_drivers);

        // Unit drivers
        $unit_drivers = [];
        foreach ($units as $unit) {
            $unit_id = $unit['id'];
            $res = DB::select("
                SELECT d.id, d.user_id, u.full_name as name, 
                       ua.unit_number as current_unit,
                       ua.plate_number as current_plate
                FROM drivers d 
                LEFT JOIN users u ON d.user_id = u.id 
                LEFT JOIN units ua ON (d.user_id = ua.driver_id OR d.user_id = ua.secondary_driver_id)
                WHERE u.role = 'driver' AND u.is_active = TRUE 
                AND ua.id = ?
                ORDER BY u.full_name
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
        $boundariesArray = [];
        foreach($boundaries as $b) {
            $boundariesArray[] = (array) $b;
        }

        return view('boundaries.index', compact(
            'boundariesArray', 'pagination', 'search',
            'date_filter', 'status_filter', 'units', 'all_drivers', 'assigned_drivers', 'unit_drivers'
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

                    Boundary::create([
                        'unit_id'         => $unit_id,
                        'driver_id'       => $driver_id,
                        'date'            => $date,
                        'boundary_amount' => $boundary_amount,
                        'actual_boundary' => $actual_boundary,
                        'shortage'        => $shortage,
                        'excess'          => $excess,
                        'status'          => $status,
                        'notes'           => $notes,
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
    public function destroy($id) { return redirect()->route('boundaries.index'); }
    public function show($id) { return redirect()->route('boundaries.index'); }
    public function create() { return redirect()->route('boundaries.index'); }
}
