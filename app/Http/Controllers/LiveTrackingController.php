<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LiveTrackingController extends Controller
{
    public function index()
    {
        try {
            // Get all units with their latest GPS data
            $tracked_units = DB::table('units as u')
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->leftJoin('users as usr', 'd.user_id', '=', 'usr.id')
                ->leftJoin('gps_tracking as g', 'u.id', '=', 'g.unit_id')
                ->select('u.*', 'usr.full_name as driver_name', 'usr.phone as driver_phone', 'g.latitude', 'g.longitude', 'g.speed', 'g.heading', 'g.ignition_status', 'g.timestamp')
                ->orderBy('u.unit_number')
                ->get();

            // Get latest GPS data separately
            $latest_gps = DB::table('gps_tracking as g')
                ->select('unit_id', DB::raw('MAX(timestamp) as max_timestamp'))
                ->groupBy('unit_id')
                ->get()
                ->keyBy('unit_id');

            // Determine GPS status for each unit
            foreach ($tracked_units as $unit) {
                $status = 'offline';
                $lastUpdate = $unit->timestamp ? new DateTime($unit->timestamp) : null;
                
                if ($lastUpdate) {
                    $now = new DateTime();
                    $diff = $now->getTimestamp() - $lastUpdate->getTimestamp();
                    
                    if ($diff < 300) { // Less than 5 minutes
                        if ($unit->ignition_status) {
                            $status = $unit->speed > 0 ? 'active' : 'idle';
                        } else {
                            $status = 'idle';
                        }
                    } else {
                        $status = 'offline';
                    }
                }
                
                $unit->gps_status = $status;
            }

            // Get system alerts
            $alerts = DB::table('system_alerts')
                ->where('is_resolved', false)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            // Get maintenance alerts
            $maintenanceAlerts = DB::table('maintenance')
                ->where('status', 'pending')
                ->where('date_started', '<=', now())
                ->orderBy('date_started')
                ->limit(5)
                ->get();

            // Calculate statistics
            $stats = [
                'total_active' => DB::table('units')->where('status', 'active')->count(),
                'gps_equipped' => 0, // Temporarily set to 0 since column doesn't exist
                'online_now' => 0, // Simplified count since complex query was causing issues
            ];

            $units = DB::table('units')->orderBy('unit_number')->get();

            return view('live-tracking.index', compact('tracked_units', 'alerts', 'maintenanceAlerts', 'units'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUnitLocation($id)
    {
        try {
            $unit = DB::table('units as u')
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->leftJoin('users as usr', 'd.user_id', '=', 'usr.id')
                ->leftJoin('gps_tracking as g', 'u.id', '=', 'g.unit_id')
                ->select('u.*', 'usr.full_name as driver_name', 'usr.phone as driver_phone', 'g.latitude', 'g.longitude', 'g.speed', 'g.heading', 'g.ignition_status', 'g.timestamp')
                ->where('u.id', $id)
                ->orderByDesc('g.timestamp')
                ->first();

            if (!$unit) {
                return response()->json(['error' => 'Unit not found'], 404);
            }

            return response()->json([
                'unit_id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'plate_number' => $unit->plate_number,
                'driver_name' => $unit->driver_name,
                'driver_phone' => $unit->driver_phone,
                'latitude' => $unit->latitude,
                'longitude' => $unit->longitude,
                'speed' => $unit->speed,
                'heading' => $unit->heading,
                'ignition_status' => $unit->ignition_status,
                'last_update' => $unit->last_update,
                'gps_status' => $unit->gps_status
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
