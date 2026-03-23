<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $type = $request->input('type', '');
        $page = max(1, (int) $request->input('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $query = DB::table('maintenance')
            ->join('units', 'maintenance.unit_id', '=', 'units.id')
            ->leftJoin('users as creator', 'maintenance.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'maintenance.updated_by', '=', 'editor.id')
            ->select('maintenance.*', 'units.unit_number', 'units.plate_number', 'creator.full_name as creator_name', 'editor.full_name as editor_name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('units.unit_number', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('units.plate_number', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('maintenance.description', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('maintenance.mechanic_name', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search]);
            });
        }

        if ($status) {
            $query->where('maintenance.status', $status);
        }

        if ($type) {
            $query->where('maintenance.maintenance_type', $type);
        }

        $total = $query->count();
        $records = $query->orderByDesc('maintenance.date_started')->offset($offset)->limit($limit)->get();

        $total_pages = max(1, ceil($total / $limit));

        $totals = DB::table('maintenance')->selectRaw('
            COUNT(*) as total_count,
            SUM(cost) as total_cost,
            SUM(CASE WHEN maintenance.status = "completed" THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN maintenance.status = "pending" THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN maintenance.status = "in_progress" THEN 1 ELSE 0 END) as in_progress_count
        ')->first();

        $units = DB::table('units')->where('status', '!=', 'retired')->orderBy('unit_number')->get();

        $pagination = [
            'page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        return view('maintenance.index', compact(
            'records',
            'search',
            'status',
            'type',
            'pagination',
            'totals',
            'units'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|integer',
            'maintenance_type' => 'required|string',
            'description' => 'required|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'odometer_reading' => 'nullable|integer',
            'date_started' => 'required|date',
            'date_completed' => 'nullable|date',
            'status' => 'required|string',
            'mechanic_name' => 'nullable|string|max:100',
            'parts_list' => 'nullable|string',
            'total_cost' => 'required|numeric|min:0',
        ]);

        $total_cost = $data['total_cost'];
        unset($data['total_cost']);

        // Update unit status if maintenance is in progress
        if (!$data['date_completed']) {
            DB::table('units')->where('id', $data['unit_id'])->update(['status' => 'maintenance']);
        }

        // Use Eloquent to trigger TrackChanges trait
        Maintenance::create(array_merge($data, [
            'cost' => $total_cost,
            'parts_list' => $data['parts_list'] ?? '',
        ]));

        return redirect()->route('maintenance.index')->with('success', 'Maintenance record added successfully');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'unit_id' => 'required|integer',
            'maintenance_type' => 'required|string',
            'description' => 'required|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'odometer_reading' => 'nullable|integer',
            'date_started' => 'required|date',
            'date_completed' => 'nullable|date',
            'status' => 'required|string',
            'mechanic_name' => 'nullable|string|max:100',
            'parts_list' => 'nullable|string',
            'total_cost' => 'required|numeric|min:0',
        ]);

        $total_cost = $data['total_cost'];
        unset($data['total_cost']);

        // Use Eloquent to trigger TrackChanges trait
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update(array_merge($data, [
            'cost' => $total_cost,
            'parts_list' => $data['parts_list'] ?? '',
        ]));

        return redirect()->route('maintenance.index')->with('success', 'Maintenance record updated successfully');
    }

    public function destroy($id)
    {
        DB::table('maintenance')->where('id', $id)->delete();
        return redirect()->route('maintenance.index')->with('success', 'Record deleted.');
    }
}
