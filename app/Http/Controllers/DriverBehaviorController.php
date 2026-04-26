<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DriverBehaviorController extends Controller
{
    // ─── Incident Types ────────────────────────────────────────────────────
    public static $incidentTypes = [
        'Coding Violation'      => ['color' => 'red',    'icon' => 'shield-alert'],
        'Late Boundary'         => ['color' => 'orange', 'icon' => 'clock'],
        'Short Boundary'        => ['color' => 'yellow', 'icon' => 'trending-down'],
        'Vehicle Damage'        => ['color' => 'purple', 'icon' => 'car-crash'],
        'Accident'              => ['color' => 'red',    'icon' => 'alert-octagon'],
        'Traffic Violation'     => ['color' => 'orange', 'icon' => 'traffic-cone'],
        'Absent / No Show'      => ['color' => 'gray',   'icon' => 'user-x'],
        'Passenger Complaint'   => ['color' => 'blue',   'icon' => 'message-square-warning'],
        'Speeding'              => ['color' => 'red',    'icon' => 'gauge'],
        'Hard Braking'          => ['color' => 'orange', 'icon' => 'zap'],
        'Other'                 => ['color' => 'gray',   'icon' => 'alert-circle'],
    ];

    // ─── INDEX: Unified Incident + Driver Dashboard ─────────────────────
    public function index(Request $request)
    {
        $search          = $request->input('search', '');
        $type_filter     = $request->input('type', '');
        $severity_filter = $request->input('severity', '');
        $date_from       = $request->input('date_from', now()->timezone('Asia/Manila')->startOfMonth()->toDateString());
        $date_to         = $request->input('date_to', now()->timezone('Asia/Manila')->toDateString());
        $tab             = $request->input('tab', 'incidents');
        $page            = max(1, (int) $request->input('page', 1));
        $limit           = 10;
        $offset          = ($page - 1) * $limit;

        // ── Unified incident feed: driver_behavior with eager loading ──
        $query = \App\Models\DriverBehavior::query()
            ->with(['involvedParties', 'partsEstimates.part'])
            ->leftJoin('units as u', 'driver_behavior.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'driver_behavior.driver_id', '=', 'd.id')
            ->select(
                'driver_behavior.*',
                'u.plate_number',
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as driver_name"),
                DB::raw("'manual' as source")
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')))"), 'like', "%{$search}%")
                  ->orWhere('u.plate_number', 'like', "%{$search}%")
                  ->orWhere('driver_behavior.incident_type', 'like', "%{$search}%")
                  ->orWhere('driver_behavior.description', 'like', "%{$search}%");
            });
        }

        if (!empty($type_filter)) {
            $query->where('driver_behavior.incident_type', $type_filter);
        }

        if (!empty($severity_filter)) {
            $query->where('driver_behavior.severity', $severity_filter);
        }

        if (!empty($date_from)) {
            $query->whereDate('driver_behavior.timestamp', '>=', $date_from);
        }
        if (!empty($date_to)) {
            $query->whereDate('driver_behavior.timestamp', '<=', $date_to);
        }

        $total     = $query->count();
        $incidents = $query->orderByDesc('driver_behavior.timestamp')->offset($offset)->limit($limit)->get();


        $pagination = [
            'page'        => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_items' => $total,
            'has_prev'    => $page > 1,
            'has_next'    => $page < ceil($total / $limit),
            'prev_page'   => $page - 1,
            'next_page'   => $page + 1,
        ];

        // ── Summary Stats ──────────────────────────────────────────────
        $stats = $this->getStats($date_from, $date_to);

        // ── Driver Performance Profiles ────────────────────────────────
        $driver_profiles = $this->getDriverProfiles($date_from, $date_to);

        // ── Incentive Eligibility ─────────────────────────────────────
        $incentive_summary = $this->getIncentiveSummary();

        // ── Dropdowns ─────────────────────────────────────────────────
        $drivers = DB::table('drivers as d')
            ->leftJoin('units as u', function($j) {
                $j->on('d.id', '=', 'u.driver_id')->orOn('d.id', '=', 'u.secondary_driver_id');
            })
            ->whereNull('d.deleted_at')
            ->select('d.id', 
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as full_name"),
                'u.plate_number as current_plate'
            )
            ->orderBy('d.last_name')->get();

        $units = DB::table('units')->whereNull('deleted_at')->where('status', '!=', 'retired')
            ->select('id', 'plate_number', 'driver_id', 'secondary_driver_id')
            ->orderBy('plate_number')->get();

        $spare_parts = \App\Models\SparePart::orderBy('name')->get();

        return view('driver-behavior.index', compact(
            'incidents', 'search', 'type_filter', 'severity_filter',
            'date_from', 'date_to', 'pagination', 'stats',
            'driver_profiles', 'incentive_summary',
            'drivers', 'units', 'tab', 'spare_parts'
        ));
    }

    // ─── STORE: Record Incident ──────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id'                => 'required|integer',
            'driver_id'              => 'required|integer',
            'incident_type'          => 'required|string',
            'severity'               => 'required|string',
            'description'            => 'required|string',
            'incident_date'          => 'nullable|date',
            'third_party_name'       => 'nullable|string',
            'third_party_vehicle'    => 'nullable|string',
            'own_unit_damage_cost'   => 'nullable|numeric|min:0',
            'third_party_damage_cost'=> 'nullable|numeric|min:0',
            'is_driver_fault'        => 'nullable|boolean',
            'total_charge_to_driver' => 'nullable|numeric|min:0',
            'charge_status'          => 'nullable|string',
            'latitude'               => 'nullable|numeric',
            'longitude'              => 'nullable|numeric',
            'video_url'              => 'nullable|string',
        ]);

        // Validate dynamic arrays without strict structure since they can be totally empty
        $parties = $request->input('parties', []);
        $parts = $request->input('parts', []);
        $cause = $request->input('cause_of_incident');

        $isFault = (bool)($data['is_driver_fault'] ?? false);
        $isAccident = in_array($data['incident_type'], ['Vehicle Damage', 'Accident']);

        // Auto-compute damages from parts list and other costs
        $computedOwnUnitDamage = 0;
        $totalCharge = 0;
        
        foreach ($parts as $partData) {
            $qty = (int)($partData['quantity'] ?? 0);
            $price = (float)($partData['unit_price'] ?? 0);
            $isCharged = (bool)($partData['is_charged_to_driver'] ?? false);
            
            $itemTotal = ($qty * $price);
            $computedOwnUnitDamage += $itemTotal;
            
            if ($isCharged) {
                $totalCharge += $itemTotal;
            }
        }

        // If parts were uploaded, it overrides the manual own_unit damage
        if (count($parts) > 0) {
            $data['own_unit_damage_cost'] = $computedOwnUnitDamage;
        }

        // Add third party damage to total charge if driver is at fault
        if ($isFault && $isAccident) {
            $totalCharge += ($data['third_party_damage_cost'] ?? 0);
        }
        
        $data['total_charge_to_driver'] = $totalCharge;

        // 1. Create main behavior record using Eloquent
        $behavior = \App\Models\DriverBehavior::create([
            'unit_id'                 => $data['unit_id'],
            'driver_id'               => $data['driver_id'],
            'incident_type'           => $data['incident_type'],
            'cause_of_incident'       => $cause,
            'severity'                => $data['severity'],
            'description'             => $data['description'],
            'third_party_name'        => collect($parties)->pluck('name')->filter()->implode(', ') ?: null, // Keep legacy synced
            'third_party_vehicle'     => collect($parties)->pluck('vehicle_type')->filter()->implode(', ') ?: null, // Keep legacy synced
            'own_unit_damage_cost'    => $data['own_unit_damage_cost'] ?? 0,
            'third_party_damage_cost' => $data['third_party_damage_cost'] ?? 0,
            'is_driver_fault'         => $isFault,
            'total_charge_to_driver'  => $totalCharge,
            'total_paid'              => 0,
            'remaining_balance'       => $totalCharge,
            'charge_status'           => $totalCharge > 0 ? 'pending' : 'none',
            'latitude'                => $data['latitude'] ?? 0,
            'longitude'               => $data['longitude'] ?? 0,
            'video_url'               => $data['video_url'] ?? '',
            'timestamp'               => now()->timezone('Asia/Manila'),
            'incident_date'           => $data['incident_date'] ?? now()->timezone('Asia/Manila')->toDateString(),
        ]);

        // 2. Insert dynamic Involved Parties
        foreach ($parties as $p) {
            if (!empty($p['name']) || !empty($p['plate_number'])) {
                \App\Models\IncidentInvolvedParty::create([
                    'driver_behavior_id' => $behavior->id,
                    'name'               => $p['name'] ?? null,
                    'vehicle_type'       => $p['vehicle_type'] ?? null,
                    'plate_number'       => $p['plate_number'] ?? null,
                ]);
            }
        }

        // 3. Insert dynamic Parts Estimates
        foreach ($parts as $partData) {
            $qty = (int)($partData['quantity'] ?? 0);
            $price = (float)($partData['unit_price'] ?? 0);
            $isCharged = (bool)($partData['is_charged_to_driver'] ?? false);
            
            if ($qty > 0 && $price >= 0) {
                \App\Models\IncidentPartsEstimate::create([
                    'driver_behavior_id' => $behavior->id,
                    'spare_part_id'      => !empty($partData['spare_part_id']) ? $partData['spare_part_id'] : null,
                    'custom_part_name'   => $partData['custom_part_name'] ?? null,
                    'quantity'           => $qty,
                    'unit_price'         => $price,
                    'total_price'        => $qty * $price,
                    'is_charged_to_driver' => $isCharged,
                ]);
            }
        }


        // If driver at fault → void incentive for any boundary on incident date
        if ($isFault && !empty($data['incident_date'])) {
            DB::table('boundaries')
                ->where('driver_id', $data['driver_id'])
                ->whereDate('date', $data['incident_date'])
                ->update([
                    'has_incentive'          => false,
                    'counted_for_incentive'  => false,
                    'notes'                  => DB::raw("CONCAT(COALESCE(notes,''), ' [Disqualified: Driver at fault in accident]')")
                ]);
        }

        // Create system alert for accidents
        if ($isAccident) {
            $unit      = DB::table('units')->find($data['unit_id']);
            $driver    = DB::table('drivers')->find($data['driver_id']);
            $plateName = $unit->plate_number ?? 'Unknown Unit';
            $driverName = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));
            DB::table('system_alerts')->insert([
                'title'       => "Accident Reported: {$plateName}",
                'message'     => "Driver {$driverName} reported an accident. Fault: " . ($isFault ? 'YES' : 'NO') . ". Charge: ₱" . number_format($data['total_charge_to_driver'] ?? 0, 2),
                'type'        => 'danger',
                'is_resolved' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()->route('driver-behavior.index', ['tab' => 'incidents'])
            ->with('success', 'Incident recorded successfully.');
    }

    // ─── SHOW: Get Incident Details (JSON) ──────────────────────────────
    public function show($id)
    {
        try {
            $incident = \App\Models\DriverBehavior::with(['driver', 'unit', 'involvedParties', 'partsEstimates'])
                ->findOrFail($id);
            
            // Map for JS compatibility
            $incident->driver_name  = trim(($incident->driver->first_name ?? '') . ' ' . ($incident->driver->last_name ?? ''));
            $incident->plate_number = $incident->unit->plate_number ?? 'N/A';
            
            return response()->json($incident);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Incident not found'], 404);
        }
    }

    // ─── UPDATE: Update Incident ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $incident = \App\Models\DriverBehavior::findOrFail($id);
        
        $data = $request->validate([
            'incident_type'          => 'required|string',
            'severity'               => 'required|string',
            'description'            => 'required|string',
            'incident_date'          => 'nullable|date',
            'is_driver_fault'        => 'nullable|boolean',
            'total_charge_to_driver' => 'nullable|numeric|min:0',
        ]);

        $prevFault = $incident->is_driver_fault;
        $prevDate  = $incident->incident_date;

        $isFault = (bool)($data['is_driver_fault'] ?? false);

        $incident->update([
            'incident_type'          => $data['incident_type'],
            'severity'               => $data['severity'],
            'description'            => $data['description'],
            'is_driver_fault'        => $isFault,
            'total_charge_to_driver' => $data['total_charge_to_driver'] ?? 0,
            'remaining_balance'      => $data['total_charge_to_driver'] ?? 0, // Reset for simplicity in this edit flow
            'incident_date'          => $data['incident_date'] ?? $incident->incident_date,
        ]);

        // Logic check: If fault changed or date changed, update boundaries
        if ($isFault != $prevFault || $incident->incident_date != $prevDate) {
            // Restore previous date incentive if it was fault and now not
            if ($prevFault) {
                DB::table('boundaries')
                    ->where('driver_id', $incident->driver_id)
                    ->whereDate('date', $prevDate)
                    ->update(['has_incentive' => true, 'counted_for_incentive' => true]);
            }
            
            // Void new date if it's now fault
            if ($isFault) {
                DB::table('boundaries')
                    ->where('driver_id', $incident->driver_id)
                    ->whereDate('date', $incident->incident_date)
                    ->update(['has_incentive' => false, 'counted_for_incentive' => false]);
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Incident updated successfully.']);
        }

        return redirect()->route('driver-behavior.index', ['tab' => 'incidents'])
            ->with('success', 'Incident updated successfully.');
    }

    // ─── DELETE (SOFT DELETE TO ARCHIVE) ───────────────────────────────
    public function destroy($id)
    {
        $behavior = \App\Models\DriverBehavior::findOrFail($id);
        $behavior->delete();
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Incident moved to Archive.']);
        }
        
        return redirect()->route('driver-behavior.index', ['tab' => 'incidents'])
            ->with('success', 'Incident moved to Archive.');
    }

    // ─── RELEASE INCENTIVE ───────────────────────────────────────────────
    public function releaseIncentive(Request $request)
    {
        $driver_id   = $request->input('driver_id');
        $release_date = now()->timezone('Asia/Manila')->toDateString();

        // Mark ALL unreleased boundaries for this driver as released (Clears shortages/late/absent)
        DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->update(['incentive_released_at' => $release_date]);

        // Mark ALL violations for this driver as released (Clears traffic/damage/accidents)
        DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->update(['incentive_released_at' => $release_date]);

        $driver = DB::table('drivers')->find($driver_id);
        $name = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));

        return redirect()->route('driver-behavior.index', ['tab' => 'incentives'])
            ->with('success', "Incentive released for {$name}. Counter reset.");
    }

    // ─── DRIVER PERFORMANCE JSON ─────────────────────────────────────────
    public function getDriverPerformance(Request $request, $driver_id)
    {
        $from = $request->input('from', now()->timezone('Asia/Manila')->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->timezone('Asia/Manila')->toDateString());

        $driver = DB::table('drivers')->find($driver_id);
        if (!$driver) return response()->json(['error' => 'Driver not found'], 404);

        $unit = DB::table('units')
            ->where(function($q) use ($driver_id) {
                $q->where('driver_id', $driver_id)
                  ->orWhere('secondary_driver_id', $driver_id);
            })
            ->whereNull('deleted_at')
            ->first();

        $incidents = DB::table('driver_behavior as db')
            ->leftJoin('units as u', 'db.unit_id', '=', 'u.id')
            ->where('db.driver_id', $driver_id)
            ->whereDate('db.timestamp', '>=', $from)
            ->whereDate('db.timestamp', '<=', $to)
            ->select('db.*', 'u.plate_number')
            ->orderByDesc('db.timestamp')
            ->limit(10)->get();

        $boundaries_count = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->count();

        $valid_days = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->count();

        $total_charges = DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->sum('total_charge_to_driver');

        $incentive = $this->computeIncentiveForDriver($driver_id, $unit);

        return response()->json([
            'driver'           => $driver,
            'unit'             => $unit,
            'incidents'        => $incidents,
            'boundaries_count' => $boundaries_count,
            'valid_days'       => $valid_days,
            'total_charges'    => $total_charges,
            'incentive'        => $incentive,
        ]);
    }

    // ─── PRIVATE: Compute Incentive For One Driver ───────────────────────
    private function computeIncentiveForDriver($driver_id, $unit)
    {
        // Determine if solo or dual
        $is_dual = $unit && !empty($unit->secondary_driver_id) && !empty($unit->driver_id);

        // Count unreleased valid boundary days
        $valid_days = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->count();

        // Violations this period (Two-Source Verification for Absolute Accuracy)
        $behavior_violations = DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->count();

        $boundary_violations = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where(function($q) {
                $q->where('shortage', '>', 0)
                  ->orWhere('has_incentive', false)
                  ->orWhere('is_absent', true);
            })
            ->whereNull('incentive_released_at')
            ->count();

        $violations = $behavior_violations + $boundary_violations;

        $required_days = 20;
        $eligible = $valid_days >= $required_days && $violations === 0;

        // Compute next payout Sunday
        $now = Carbon::now('Asia/Manila');
        
        // Find the unit ID for staggering dual drivers
        $unitId = $unit->id ?? 0;

        if ($is_dual) {
            // Dual Driver: 2-month cycle staggered by Unit ID.
            // Split dual drivers into 2 groups so they don't all pay on the same month.
            // Group A (Odd Unit ID): Pays in ODD months (Jan, Mar, May, Jul, Sep, Nov)
            // Group B (Even Unit ID): Pays in EVEN months (Feb, Apr, Jun, Aug, Oct, Dec)
            
            $isOddUnit = ($unitId % 2 !== 0);
            $currentMonth = $now->month;
            
            // Determine if THIS month is the payout month for this unit
            $isPayoutMonth = ($isOddUnit && ($currentMonth % 2 !== 0)) || (!$isOddUnit && ($currentMonth % 2 === 0));
            
            if ($isPayoutMonth) {
                // Check if the 1st Sunday of THIS month has already passed
                $firstSunday = $now->copy()->startOfMonth();
                while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
                
                if ($now->gt($firstSunday->endOfDay())) {
                    // Passed -> Next natural payout is in 2 months
                    $targetMonth = $now->copy()->addMonths(2)->startOfMonth();
                } else {
                    // Not passed -> Natural payout is THIS month
                    $targetMonth = $now->copy()->startOfMonth();
                }
            } else {
                // Not the payout month -> Payout is NEXT month
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            }

            // [NEW] Skip logic: If there are violations, jump one more 2-month cycle
            if ($violations > 0) {
                $targetMonth->addMonths(2);
            }

        } else {
            // Solo Driver: Strictly 1 month cycle. Payout is every 1st Sunday.
            $firstSunday = $now->copy()->startOfMonth();
            while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
            
            if ($now->gt($firstSunday->endOfDay())) {
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            } else {
                $targetMonth = $now->copy()->startOfMonth();
            }

            // [NEW] Skip logic: If there are violations, jump one more month
            if ($violations > 0) {
                $targetMonth->addMonth();
            }
        }

        // Find the 1st Sunday of the target month
        $payoutDate = $targetMonth->copy();
        while ($payoutDate->dayOfWeek !== Carbon::SUNDAY) {
            $payoutDate->addDay();
        }

        return [
            'is_dual'          => $is_dual,
            'valid_days'       => $valid_days,
            'violations'       => $violations,
            'eligible'         => $eligible,
            'next_payout_date' => $payoutDate->format('M d, Y'),
            'required_days'    => $required_days,
            'driver_type'      => $is_dual ? 'Dual Driver' : 'Solo Driver',
        ];
    }

    // ─── PRIVATE: Summary Stats ─────────────────────────────────────────
    private function getStats($from, $to)
    {
        $base  = DB::table('driver_behavior')->whereDate('timestamp', '>=', $from)->whereDate('timestamp', '<=', $to);
        $bySev = (clone $base)->selectRaw('severity, COUNT(*) as count')->groupBy('severity')->get()->pluck('count', 'severity')->toArray();
        $byType = (clone $base)->selectRaw('incident_type, COUNT(*) as count')->groupBy('incident_type')->orderByDesc('count')->limit(8)->get();

        $totalViolators = DB::table('driver_behavior')
            ->whereDate('timestamp', '>=', $from)
            ->whereDate('timestamp', '<=', $to)
            ->distinct('driver_id')
            ->count('driver_id');

        $totalCharges   = DB::table('driver_behavior')->sum('total_charge_to_driver');
        $pendingCharges = DB::table('driver_behavior')->where('charge_status', 'pending')->sum('total_charge_to_driver');
        
        $violationsToday = DB::table('driver_behavior')
            ->whereDate('timestamp', now()->toDateString())
            ->count();

        $violatorsToday = DB::table('driver_behavior')
            ->whereDate('timestamp', now()->toDateString())
            ->distinct('driver_id')
            ->count('driver_id');

        $chargesThisMonth = DB::table('driver_behavior')
            ->whereMonth('timestamp', now()->month)
            ->whereYear('timestamp', now()->year)
            ->sum('total_charge_to_driver');

        $chargesLastMonth = DB::table('driver_behavior')
            ->whereMonth('timestamp', now()->subMonth()->month)
            ->whereYear('timestamp', now()->subMonth()->year)
            ->sum('total_charge_to_driver');

        // Logic for "Eligible Last Month" (Drivers with no violations in the previous calendar month)
        $startLastMonth = now()->subMonth()->startOfMonth()->toDateString();
        $endLastMonth = now()->subMonth()->endOfMonth()->toDateString();
        
        $allDrivers = DB::table('drivers')->whereNull('deleted_at')->pluck('id');
        $violatorsLastMonth = DB::table('driver_behavior')
            ->whereBetween('timestamp', [$startLastMonth, $endLastMonth])
            ->distinct('driver_id')
            ->pluck('driver_id');
            
        $eligibleLastMonth = $allDrivers->diff($violatorsLastMonth)->count();

        return [
            'incidents_period'    => (clone $base)->count(),
            'violations_today'    => $violationsToday,
            'violators_today'     => $violatorsToday,
            'by_severity'         => $bySev,
            'incident_types'      => $byType,
            'total_violators'     => $totalViolators,
            'total_charges'       => $totalCharges,
            'pending_charges'     => $pendingCharges,
            'charges_this_month'  => $chargesThisMonth,
            'charges_last_month'  => $chargesLastMonth,
            'eligible_last_month' => $eligibleLastMonth,
        ];
    }

    // ─── PRIVATE: Driver Profiles ───────────────────────────────────────
    private function getDriverProfiles($from, $to)
    {
        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select(
                'd.id', 'd.first_name', 'd.last_name', 'd.driver_status',
                'u.id as unit_id', 'u.plate_number', 'u.driver_id', 'u.secondary_driver_id'
            )
            ->distinct('d.id')
            ->get();

        // ── OPTIMIZATION: Bulk fetch statistics to avoid N+1 queries ──
        $incidentCounts = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('count(*) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $debtSum = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('sum(remaining_balance) as aggregate'))
            ->where('charge_status', 'pending')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $boundaryCounts = DB::table('boundaries')
            ->select('driver_id', DB::raw('count(*) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $shortageSum = DB::table('boundaries')
            ->select('driver_id', DB::raw('sum(shortage) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $chargeSum = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('sum(total_charge_to_driver) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $profiles = [];
        foreach ($drivers as $d) {
            $unit_obj  = (object)['driver_id' => $d->driver_id ?? null, 'secondary_driver_id' => $d->secondary_driver_id ?? null, 'plate_number' => $d->plate_number ?? null, 'id' => $d->unit_id];
            
            // We still need this for complex date logic, but the statistics are now fast.
            $incentive = $this->computeIncentiveForDriver($d->id, $unit_obj);

            $profiles[] = [
                'id'          => $d->id,
                'name'        => trim($d->first_name . ' ' . $d->last_name),
                'status'      => $d->driver_status,
                'unit'        => $d->plate_number,
                'unit_id'     => $d->unit_id,
                'incidents'   => $incidentCounts[$d->id] ?? 0,
                'boundaries'  => $boundaryCounts[$d->id] ?? 0,
                'shortages'   => $shortageSum[$d->id] ?? 0,
                'charges'     => $chargeSum[$d->id] ?? 0,
                'total_debt'  => $debtSum[$d->id] ?? 0,
                'incentive'   => $incentive,
            ];
        }

        return collect($profiles)->sortBy('name')->values();
    }

    // ─── PRIVATE: Incentive Summary ─────────────────────────────────────
    private function getIncentiveSummary()
    {
        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select('d.id', 'd.first_name', 'd.last_name', 'u.plate_number', 'u.driver_id', 'u.secondary_driver_id')
            ->distinct('d.id')->get();

        $eligible   = [];
        $ineligible = [];

        foreach ($drivers as $d) {
            $unit = (object)['driver_id' => $d->driver_id ?? null, 'secondary_driver_id' => $d->secondary_driver_id ?? null];
            
            // This method is still called in a loop, but computeIncentiveForDriver 
            // is the core business logic. The previous optimization in getDriverProfiles 
            // addressed the most egregious N+1. Here we compute the full incentive status.
            $inc = $this->computeIncentiveForDriver($d->id, $unit);

            $row = [
                'driver_id'     => $d->id,
                'name'          => trim($d->first_name . ' ' . $d->last_name),
                'unit'          => $d->plate_number,
                'driver_type'   => $inc['driver_type'],
                'valid_days'    => $inc['valid_days'],
                'violations'    => $inc['violations'],
                'eligible'      => $inc['eligible'],
                'next_payout'   => $inc['next_payout_date'],
            ];

            if ($inc['eligible']) {
                $eligible[] = $row;
            } else {
                $ineligible[] = $row;
            }
        }

        return ['eligible' => $eligible, 'ineligible' => $ineligible];
    }

    public function getStatistics(Request $request)
    {
        // Kept for backward compatibility
        return response()->json($this->getStats(now()->subDays(30)->toDateString(), now()->toDateString()));
    }
}
