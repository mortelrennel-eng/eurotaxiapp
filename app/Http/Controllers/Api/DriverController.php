<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    /**
     * Display a listing of the drivers.
     */
    public function index()
    {
        $drivers = Driver::with('user')->get()->map(function($driver) {
            return [
                'id' => $driver->id,
                'name' => $driver->user->full_name ?? $driver->user->name,
                'email' => $driver->user->email,
                'phone' => $driver->contact_number,
                'license' => $driver->license_number,
                'status' => $driver->user->is_active ? 'Active' : 'Inactive',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $drivers,
        ]);
    }
}
