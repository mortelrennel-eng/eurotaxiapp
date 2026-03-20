<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Unit;
use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status_filter = $request->input('status', '');
        $page = max(1, (int) $request->input('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $query = DB::table('units as u')
            ->leftJoin('users as usr1', 'u.driver_id', '=', 'usr1.id')
            ->leftJoin('drivers as drv1', 'usr1.id', '=', 'drv1.user_id')
            ->leftJoin('users as usr2', 'u.secondary_driver_id', '=', 'usr2.id')
            ->leftJoin('drivers as drv2', 'usr2.id', '=', 'drv2.user_id')
            ->select('u.*', 'usr1.full_name as driver1_name', 'usr2.full_name as driver2_name');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('u.unit_number', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('u.plate_number', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('u.make', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('u.model', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search]);
            });
        }

        if (!empty($status_filter)) {
            $query->where('u.status', $status_filter);
        }

        $total_units = $query->count();
        $units = $query->orderBy('u.unit_number')->offset($offset)->limit($limit)->get();

        $total_pages = ceil($total_units / $limit);
        $pagination = [
            'page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_units,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        // Drivers list for add/edit modal
        $all_drivers = DB::table('users as u')
            ->join('drivers as d', 'u.id', '=', 'd.user_id')
            ->where('u.is_active', true)
            ->select('u.id', 'u.full_name', 'd.contact_number', 'd.license_number')
            ->get();

        return view('units.index', compact('units', 'pagination', 'search', 'status_filter', 'all_drivers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_number' => 'required|string|unique:units,unit_number',
            'plate_number' => 'required|string|unique:units,plate_number',
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer',
            'status' => 'required|string',
            'boundary_rate' => 'required|numeric',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'color' => 'nullable|string',
            'unit_type' => 'required|in:new,old,rented',
            'fuel_status' => 'required|string',
            'coding_day' => 'nullable|string',
            'driver_id' => 'nullable|integer',
            'secondary_driver_id' => 'nullable|integer',
        ]);

        $driver_id = $request->input('driver_id') ?: null;
        $secondary_driver_id = $request->input('secondary_driver_id') ?: null;

        // Check driver conflict
        if ($driver_id) {
            $conflict = DB::table('units')
                ->where(function ($q) use ($driver_id) {
                    $q->where('driver_id', $driver_id)->orWhere('secondary_driver_id', $driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected primary driver is already assigned to unit ' . $conflict->unit_number . '.');
            }
        }
        if ($secondary_driver_id) {
            $conflict = DB::table('units')
                ->where(function ($q) use ($secondary_driver_id) {
                    $q->where('driver_id', $secondary_driver_id)->orWhere('secondary_driver_id', $secondary_driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected secondary driver is already assigned to unit ' . $conflict->unit_number . '.');
            }
        }

        // Auto set coding status
        $status = $data['status'];
        $coding_day = $data['coding_day'] ?? null;
        if ($coding_day && date('l') === $coding_day) {
            $status = 'coding';
        }

        DB::table('units')->insert([
            'unit_number' => $data['unit_number'],
            'plate_number' => $data['plate_number'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'],
            'status' => $status,
            'boundary_rate' => $data['boundary_rate'],
            'purchase_date' => $data['purchase_date'] ?? null,
            'purchase_cost' => $data['purchase_cost'] ?? 0,
            'color' => $data['color'] ?? null,
            'unit_type' => $data['unit_type'],
            'fuel_status' => $data['fuel_status'],
            'coding_day' => $coding_day,
            'driver_id' => $driver_id,
            'secondary_driver_id' => $secondary_driver_id,
            'coding_updated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('units.index')->with('success', 'Unit added successfully!');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'unit_number' => 'required|string|unique:units,unit_number,' . $id,
            'plate_number' => 'required|string|unique:units,plate_number,' . $id,
            'make' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer',
            'status' => 'required|string',
            'boundary_rate' => 'required|numeric',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'color' => 'nullable|string',
            'unit_type' => 'required|in:new,old,rented',
            'fuel_status' => 'required|string',
            'coding_day' => 'nullable|string',
            'driver_id' => 'nullable|integer',
            'secondary_driver_id' => 'nullable|integer',
        ]);

        $driver_id = $request->input('driver_id') ?: null;
        $secondary_driver_id = $request->input('secondary_driver_id') ?: null;

        // Check driver conflict (excluding this unit)
        if ($driver_id) {
            $conflict = DB::table('units')
                ->where('id', '!=', $id)
                ->where(function ($q) use ($driver_id) {
                    $q->where('driver_id', $driver_id)->orWhere('secondary_driver_id', $driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected primary driver is already assigned to unit ' . $conflict->unit_number . '.');
            }
        }
        if ($secondary_driver_id) {
            $conflict = DB::table('units')
                ->where('id', '!=', $id)
                ->where(function ($q) use ($secondary_driver_id) {
                    $q->where('driver_id', $secondary_driver_id)->orWhere('secondary_driver_id', $secondary_driver_id);
                })->first();
            if ($conflict) {
                return back()->with('error', 'Selected secondary driver is already assigned to unit ' . $conflict->unit_number . '.');
            }
        }

        // Auto set coding status
        $status = $data['status'];
        $coding_day = $data['coding_day'] ?? null;
        if ($coding_day && date('l') === $coding_day) {
            $status = 'coding';
        }

        DB::table('units')->where('id', $id)->update([
            'unit_number' => $data['unit_number'],
            'plate_number' => $data['plate_number'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'],
            'status' => $status,
            'boundary_rate' => $data['boundary_rate'],
            'purchase_date' => $data['purchase_date'] ?? null,
            'purchase_cost' => $data['purchase_cost'] ?? 0,
            'color' => $data['color'] ?? null,
            'unit_type' => $data['unit_type'],
            'fuel_status' => $data['fuel_status'],
            'coding_day' => $coding_day,
            'driver_id' => $driver_id,
            'secondary_driver_id' => $secondary_driver_id,
            'updated_at' => now(),
        ]);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully!');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            DB::table('gps_devices')->where('unit_id', $id)->delete();
            DB::table('dashcam_devices')->where('unit_id', $id)->delete();
            DB::table('units')->where('id', $id)->delete();
            DB::commit();
            return redirect()->route('units.index')->with('success', 'Unit deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('units.index')->with('error', 'Failed to delete unit: ' . $e->getMessage());
        }
    }

    public function getDetails(Request $request)
    {
        $unit_id = (int) $request->input('id', 0);
        if (!$unit_id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $unit = DB::table('units')->where('id', $unit_id)->first();
        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        // Assigned drivers
        $assigned_drivers = [];
        $driver_ids = array_filter([(int) ($unit->driver_id ?? 0), (int) ($unit->secondary_driver_id ?? 0)]);
        if (!empty($driver_ids)) {
            $assigned_drivers = DB::table('users as u')
                ->join('drivers as d', 'u.id', '=', 'd.user_id')
                ->whereIn('u.id', $driver_ids)
                ->select('u.id', 'u.full_name', 'u.email', 'd.license_number', 'd.contact_number', 'd.license_expiry', 'd.hire_date', 'd.daily_boundary_target')
                ->get()->toArray();
        }

        // ROI data from real boundaries
        $roi = DB::table('boundaries')
            ->where('unit_id', $unit_id)
            ->selectRaw('
                SUM(boundary_amount) as total_boundary,
                SUM(CASE WHEN MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE()) THEN boundary_amount ELSE 0 END) as monthly_boundary,
                SUM(CASE WHEN status IN ("paid","excess") THEN boundary_amount ELSE 0 END) as paid_boundary
            ')->first();

        $maintenance_cost = DB::table('maintenance')
            ->where('unit_id', $unit_id)
            ->selectRaw('SUM(cost) as total, SUM(CASE WHEN MONTH(date_started)=MONTH(CURDATE()) AND YEAR(date_started)=YEAR(CURDATE()) THEN cost ELSE 0 END) as monthly')
            ->first();


        $total_investment = $unit->purchase_cost ?? 0;
        $total_revenue = $roi->paid_boundary ?? 0;
        $total_expenses = $maintenance_cost->total ?? 0;
        $monthly_revenue = $roi->monthly_boundary ?? 0;
        $roi_percentage = $total_investment > 0 ? (($total_revenue - $total_expenses) / $total_investment) * 100 : 0;
        $payback_period = $monthly_revenue > 0 ? $total_investment / $monthly_revenue : 0;

        $roi_data = [
            'total_investment' => $total_investment,
            'total_revenue' => $total_revenue,
            'total_expenses' => $total_expenses,
            'monthly_revenue' => $monthly_revenue,
            'monthly_expenses' => $maintenance_cost->monthly ?? 0,
            'roi_percentage' => round($roi_percentage, 2),
            'payback_period' => round($payback_period, 2),
            'monthly_boundary' => $roi->monthly_boundary ?? 0,
            'total_boundary' => $roi->total_boundary ?? 0,
        ];

        // Boundary history (last 10 records from boundaries table)
        $boundary_history = DB::table('boundaries as bh')
            ->leftJoin('users as usr', 'bh.driver_id', '=', 'usr.id')
            ->where('bh.unit_id', $unit_id)
            ->select('bh.*', 'usr.full_name')
            ->orderByDesc('bh.date')
            ->limit(10)->get()->toArray();

        // Maintenance records from real maintenance table
        $maintenance_records = DB::table('maintenance as mr')
            ->where('mr.unit_id', $unit_id)
            ->select('mr.*')
            ->orderByDesc('mr.date_started')
            ->limit(10)->get()->toArray();


        // Coding info
        $last_digit = substr($unit->plate_number ?? '', -1);
        $coding_schedule = [
            'Monday' => [1, 2],
            'Tuesday' => [3, 4],
            'Wednesday' => [5, 6],
            'Thursday' => [7, 8],
            'Friday' => [9, 0],
        ];
        $coding_day = 'Not Set';
        foreach ($coding_schedule as $day => $endings) {
            if (in_array((int) $last_digit, $endings)) {
                $coding_day = $day;
                break;
            }
        }

        return response()->json([
            'unit' => $unit,
            'assigned_drivers' => $assigned_drivers,
            'roi_data' => $roi_data,
            'boundary_history' => $boundary_history,
            'maintenance_records' => $maintenance_records,
            'coding_day' => $coding_day,
            'location_info' => [
                'current_location' => 'Not Available',
                'last_location_update' => 'Never',
                'gps_enabled' => false,
                'coordinates' => null,
            ],
            'dashcam_info' => [
                'dashcam_enabled' => false,
                'dashcam_status' => 'Offline',
                'last_recording' => 'Never',
                'storage_used' => 0,
                'storage_total' => 32,
            ],
        ]);
    }

    public function getDetailsHtml(Request $request)
    {
        $unit_id = (int) $request->input('id', 0);
        if (!$unit_id) {
            return response('Invalid ID', 400);
        }

        $unit = DB::table('units')->where('id', $unit_id)->first();
        if (!$unit) {
            return response('Unit not found', 404);
        }

        $assigned_drivers = [];
        $driver_ids = array_filter([(int) ($unit->driver_id ?? 0), (int) ($unit->secondary_driver_id ?? 0)]);
        if (!empty($driver_ids)) {
            $assigned_drivers = DB::table('users as u')
                ->join('drivers as d', 'u.id', '=', 'd.user_id')
                ->whereIn('u.id', $driver_ids)
                ->select('u.id', 'u.full_name', 'u.email', 'd.license_number', 'd.contact_number', 'd.license_expiry', 'd.hire_date', 'd.daily_boundary_target')
                ->get()->toArray();
        }

        $roi = DB::table('boundaries')
            ->where('unit_id', $unit_id)
            ->selectRaw('
                SUM(boundary_amount) as total_boundary,
                SUM(CASE WHEN MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE()) THEN boundary_amount ELSE 0 END) as monthly_boundary,
                SUM(CASE WHEN status IN ("paid","excess") THEN boundary_amount ELSE 0 END) as paid_boundary
            ')->first();

        $maintenance_cost = DB::table('maintenance')
            ->where('unit_id', $unit_id)
            ->selectRaw('SUM(cost) as total, SUM(CASE WHEN MONTH(date_started)=MONTH(CURDATE()) AND YEAR(date_started)=YEAR(CURDATE()) THEN cost ELSE 0 END) as monthly')
            ->first();

        $total_investment = $unit->purchase_cost ?? 0;
        $total_revenue = $roi->paid_boundary ?? 0;
        $total_expenses = $maintenance_cost->total ?? 0;
        $monthly_revenue = $roi->monthly_boundary ?? 0;
        $roi_percentage = $total_investment > 0 ? (($total_revenue - $total_expenses) / $total_investment) * 100 : 0;
        $payback_period = $monthly_revenue > 0 ? $total_investment / $monthly_revenue : 0;

        $roi_data = [
            'total_investment' => $total_investment,
            'total_revenue' => $total_revenue,
            'total_expenses' => $total_expenses,
            'monthly_revenue' => $monthly_revenue,
            'monthly_expenses' => $maintenance_cost->monthly ?? 0,
            'roi_percentage' => round($roi_percentage, 2),
            'payback_period' => round($payback_period, 2),
            'monthly_boundary' => $roi->monthly_boundary ?? 0,
            'total_boundary' => $roi->total_boundary ?? 0,
        ];

        $boundary_history = DB::table('boundaries as bh')
            ->leftJoin('users as usr', 'bh.driver_id', '=', 'usr.id')
            ->where('bh.unit_id', $unit_id)
            ->select('bh.*', 'usr.full_name')
            ->orderByDesc('bh.date')
            ->limit(10)->get()->toArray();

        $maintenance_records = DB::table('maintenance as mr')
            ->where('mr.unit_id', $unit_id)
            ->select('mr.*')
            ->orderByDesc('mr.date_started')
            ->limit(10)->get()->toArray();

        $last_digit = substr($unit->plate_number ?? '', -1);
        $coding_schedule = [
            'Monday' => [1, 2],
            'Tuesday' => [3, 4],
            'Wednesday' => [5, 6],
            'Thursday' => [7, 8],
            'Friday' => [9, 0],
        ];
        $coding_day = 'Not Set';
        foreach ($coding_schedule as $day => $endings) {
            if (in_array((int) $last_digit, $endings)) {
                $coding_day = $day;
                break;
            }
        }

        $next_coding_date = '';
        $days_until_coding = 0;
        if ($coding_day !== 'Not Set') {
            $today = Carbon::today();
            if ($today->format('l') === $coding_day) {
                $next_coding_date = $today->format('M d, Y');
                $days_until_coding = 0;
            } else {
                $target = Carbon::parse('next ' . $coding_day);
                $next_coding_date = $target->format('M d, Y');
                $days_until_coding = $today->diffInDays($target);
            }
        }

        $location_info = [
            'current_location' => data_get($unit, 'current_location', 'Unknown'),
            'last_location_update' => data_get($unit, 'last_location_update', 'Never'),
            'gps_enabled' => (bool) data_get($unit, 'gps_enabled', false),
            'coordinates' => (data_get($unit, 'latitude') && data_get($unit, 'longitude')) ? (data_get($unit, 'latitude') . ', ' . data_get($unit, 'longitude')) : null,
        ];

        $dashcam_info = [
            'dashcam_enabled' => (bool) data_get($unit, 'dashcam_enabled', false),
            'dashcam_status' => data_get($unit, 'dashcam_status', 'Offline'),
            'last_recording' => data_get($unit, 'last_recording', 'Never'),
            'storage_used' => (float) data_get($unit, 'storage_used', 0),
            'storage_total' => (float) data_get($unit, 'storage_total', 0),
        ];

        return view('units.partials.unit_details_modal', compact(
            'unit',
            'assigned_drivers',
            'boundary_history',
            'maintenance_records',
            'roi_data',
            'coding_day',
            'next_coding_date',
            'days_until_coding',
            'location_info',
            'dashcam_info'
        ));
    }

    public function toggleStatus(Request $request)
    {
        $id = $request->input('id');
        $new_status = $request->input('new_status', 'active');

        DB::table('units')->where('id', $id)->update([
            'status' => $new_status,
            'updated_at' => now(),
        ]);

        return redirect()->route('units.index')->with('success', 'Unit status updated!');
    }

    public function showImport()
    {
        return view('units.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:10240'
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        
        try {
            if ($extension == 'csv') {
                $this->importCSV($file);
            } else {
                $this->importExcel($file);
            }
            
            return redirect()->route('units.index')->with('success', 'Units imported successfully!');
        } catch (\Exception $e) {
            return redirect()->route('units.index')->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    private function importCSV($file)
    {
        $csvData = array_map('str_getcsv', file($file->getPathname()));
        $headers = array_shift($csvData);
        
        foreach ($csvData as $row) {
            if (count($row) >= 4) {
                DB::table('units')->insert([
                    'unit_number' => $row[0] ?? '',
                    'plate_number' => $row[1] ?? '',
                    'make' => $row[2] ?? '',
                    'model' => $row[3] ?? '',
                    'status' => $row[4] ?? 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function importExcel($file)
    {
        // For now, treat as CSV (you can install phpoffice/phpspreadsheet for full Excel support)
        $this->importCSV($file);
    }
}
