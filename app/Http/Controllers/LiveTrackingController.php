<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveTrackingController extends Controller
{
    // ─── Main Page ─────────────────────────────────────────

    public function index()
    {
        try {
            $units = DB::table('units as u')
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->leftJoin('users as usr', 'd.user_id', '=', 'usr.id')
                ->select(
                    'u.id', 'u.unit_number', 'u.plate_number', 'u.make', 'u.model',
                    'u.status', 'u.gps_link',
                    'usr.full_name as driver_name', 'usr.phone as driver_phone'
                )
                ->orderBy('u.unit_number')
                ->get();

            // Assign a simplified GPS status logic based on URL presence
            $tracked_units = $units->map(function ($unit) {
                $hasGps = !empty($unit->gps_link);
                $unit->gps_status = $hasGps ? 'active' : 'offline';
                $unit->latitude = null; // No raw GPS data available
                $unit->longitude = null;
                $unit->speed = 0;
                $unit->current_driver = $unit->driver_name ?? 'None';
                return $unit;
            });

            // Simulated stats since we don't have real-time API
            $stats = [
                'total'     => $tracked_units->count(),
                'active'    => $tracked_units->where('gps_status', 'active')->count(),
                'idle'      => 0,
                'offline'   => $tracked_units->where('gps_status', 'offline')->count(),
                'avg_speed' => 0
            ];

            $alerts = collect();
            $maintenanceAlerts = collect();

            return view('live-tracking.index', compact('tracked_units', 'alerts', 'maintenanceAlerts', 'units', 'stats'));

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── AJAX: All Units (for auto-refresh) ────────────────

    public function getUnitsLive()
    {
        try {
            $units = DB::table('units as u')
                ->leftJoin('drivers as d', 'u.driver_id', '=', 'd.id')
                ->leftJoin('users as usr', 'd.user_id', '=', 'usr.id')
                ->select(
                    'u.id', 'u.unit_number', 'u.plate_number',
                    'u.gps_link', 'u.status',
                    'usr.full_name as driver_name', 'usr.phone as driver_phone'
                )
                ->orderBy('u.unit_number')
                ->get();

            $result = $units->map(function ($unit) {
                $hasGps = !empty($unit->gps_link);
                return [
                    'unit_id'       => $unit->id,
                    'unit_number'   => $unit->unit_number,
                    'plate_number'  => $unit->plate_number,
                    'driver_name'   => $unit->driver_name   ?? 'None',
                    'driver_phone'  => $unit->driver_phone  ?? '',
                    'has_gps'       => $hasGps,
                    'gps_link'      => $unit->gps_link,
                    'gps_status'    => $hasGps ? 'active' : 'offline',
                ];
            });

            return response()->json(['success' => true, 'units' => $result]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── AJAX: Single Unit ─────────────────────────────────

    public function getUnitLocation($id)
    {
        try {
            $unit = DB::table('units')->find($id);
            if (!$unit || empty($unit->gps_link)) {
                return response()->json(['success' => false, 'error' => 'No GPS link assigned']);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'gps_link' => $unit->gps_link,
                    'status'   => 'active',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
