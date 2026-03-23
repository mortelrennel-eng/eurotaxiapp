<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $page   = max(1, (int) $request->input('page', 1));
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        // Build base query — no GROUP BY, use correlated subquery for unit assignment
        // units.driver_id references users.id (not drivers.id)
        $query = DB::table('drivers as d')
            ->join('users as u', 'd.user_id', '=', 'u.id')
            ->leftJoin('users as creator', 'd.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'd.updated_by', '=', 'editor.id')
            ->select(
                'd.id', 'd.user_id', 'd.license_number', 'd.license_expiry',
                'd.contact_number', 'd.hire_date', 'd.daily_boundary_target',
                'd.driver_type', 'd.driver_status',
                'd.emergency_contact', 'd.emergency_phone',
                'u.full_name', 'u.email', 'u.username', 'u.is_active', 'u.phone',
                'creator.full_name as creator_name', 'editor.full_name as editor_name',
                DB::raw('(SELECT unit_number FROM units WHERE driver_id = u.id OR secondary_driver_id = u.id LIMIT 1) as unit_number'),
                DB::raw('(SELECT plate_number FROM units WHERE driver_id = u.id OR secondary_driver_id = u.id LIMIT 1) as plate_number')
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.full_name', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                  ->orWhere('d.license_number', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                  ->orWhere('u.email', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search]);
            });
        }

        $total       = $query->count();
        $drivers     = $query->orderBy('u.full_name')->offset($offset)->limit($limit)->get();
        $total_pages = max(1, ceil($total / $limit));

        // Stats
        $stats = [
            'total'     => DB::table('drivers')->count(),
            'available' => DB::table('drivers')->where('driver_status', 'available')->count(),
            'assigned'  => DB::table('drivers')->where('driver_status', 'assigned')->count(),
            'on_leave'  => DB::table('drivers')->where('driver_status', 'on_leave')->count(),
        ];

        // Expiring licenses within 30 days
        $expiring_licenses = DB::table('drivers as d')
            ->join('users as u', 'd.user_id', '=', 'u.id')
            ->whereRaw('d.license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
            ->select('u.full_name', 'd.license_number', 'd.license_expiry')
            ->get();

        $pagination = [
            'page'        => $page,
            'total_pages' => $total_pages,
            'total_items' => $total,
            'has_prev'    => $page > 1,
            'has_next'    => $page < $total_pages,
            'prev_page'   => $page - 1,
            'next_page'   => $page + 1,
        ];

        return view('driver-management.index', compact(
            'drivers', 'search', 'pagination', 'stats', 'expiring_licenses'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:100',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
            'license_number' => 'required|string|max:50|unique:drivers,license_number',
            'license_expiry' => 'required|date',
            'emergency_contact' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
            'hire_date' => 'required|date',
            'daily_boundary_target' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Auto-generate system credentials
            $baseSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $request->full_name));
            if ($baseSlug === '') {
                $baseSlug = 'driver';
            }
            $suffix = time();
            $username = substr($baseSlug, 0, 12) . $suffix;
            $email = $username . '@driver.local';

            // Generate a random password and hash it
            $rawPassword = bin2hex(random_bytes(4)); // 8 hex chars
            $password_hash = Hash::make($rawPassword);

            // Create user account using Eloquent
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $email,
                'username' => $username,
                'password' => $password_hash,
                'role' => 'driver',
                'is_active' => 1,
            ]);
            $userId = $user->id;

            // Create driver record using Eloquent to trigger TrackChanges trait
            Driver::create([
                'user_id' => $userId,
                'license_number' => $request->license_number,
                'license_expiry' => $request->license_expiry,
                'contact_number' => $request->contact_number,
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'hire_date' => $request->hire_date,
                'daily_boundary_target' => $request->daily_boundary_target,
            ]);

            DB::commit();

            return redirect()->route('driver-management.index')
                ->with('success', "Driver added successfully! Username: {$username}, Password: {$rawPassword}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add driver: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $driver = DB::table('drivers')->where('id', $id)->first();
        if (!$driver) {
            return redirect()->route('driver-management.index')->with('error', 'Driver not found.');
        }

        $request->validate([
            'full_name' => 'required|string|max:100',
            'contact_number' => 'required|string|max:20',
            'address' => 'required|string',
            'license_number' => 'required|string|max:50',
            'license_expiry' => 'required|date',
            'emergency_contact' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:20',
            'hire_date' => 'required|date',
            'daily_boundary_target' => 'required|numeric|min:0',
            'driver_type' => 'nullable|in:regular,senior,trainee',
            'driver_status' => 'nullable|in:available,assigned,on_leave,suspended',
        ]);

        // Use Eloquent to trigger TrackChanges trait
        $driver_instance = Driver::findOrFail($id);
        
        $user_instance = User::findOrFail($driver_instance->user_id);
        $user_instance->update([
            'full_name' => $request->full_name,
        ]);

        $driver_instance->update([
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'contact_number' => $request->contact_number,
            'address' => $request->address,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
            'hire_date' => $request->hire_date,
            'daily_boundary_target' => $request->daily_boundary_target,
            'driver_type' => $request->driver_type ?? 'regular',
            'driver_status' => $request->driver_status ?? 'available',
        ]);

        return redirect()->route('driver-management.index')->with('success', 'Driver updated successfully');
    }

    public function destroy($id)
    {
        $driver = DB::table('drivers')->where('id', $id)->first();
        if ($driver) {
            DB::beginTransaction();
            try {
                // Unassign from units
                DB::table('units')->where('driver_id', $driver->user_id)->update(['driver_id' => null]);
                DB::table('units')->where('secondary_driver_id', $driver->user_id)->update(['secondary_driver_id' => null]);
                
                // Delete driver and user records
                DB::table('drivers')->where('id', $id)->delete();
                DB::table('users')->where('id', $driver->user_id)->delete();
                
                DB::commit();
                return redirect()->route('driver-management.index')->with('success', 'Driver removed successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('driver-management.index')->with('error', 'Failed to remove driver: ' . $e->getMessage());
            }
        }
        return redirect()->route('driver-management.index')->with('error', 'Driver not found.');
    }

    public function uploadDocuments(Request $request, $id)
    {
        $request->validate([
            'license_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nbi_clearance' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $driver = DB::table('drivers')->where('id', $id)->first();
        if (!$driver) {
            return back()->with('error', 'Driver not found.');
        }

        // Handle file uploads (simplified for this example)
        $uploadedFiles = [];
        foreach (['license_scan', 'nbi_clearance', 'medical_certificate'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/drivers'), $filename);
                $uploadedFiles[] = $filename;
            }
        }

        return back()->with('success', 'Documents uploaded successfully!');
    }
}
